<?php

namespace App\Services\Stats\Normalizers;

use App\Services\Stats\Contracts\StatNormalizerInterface;
use App\Services\Stats\DTO\NormalizedRowDTO;
use App\Services\Stats\DTO\NormalizedTableDTO;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiNormalizer implements StatNormalizerInterface
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY'));
        $this->model = config('services.openai.model', env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'));
        $this->baseUrl = config('services.openai.base_url', env('OPENAI_BASE_URL', 'https://api.openai.com/v1'));
    }

    /**
     * Normalizuje HTML fragment pomocí OpenAI API.
     */
    public function normalize(string $content, array $mappingConfig): NormalizedTableDTO
    {
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key is not configured.');
        }

        $type = $mappingConfig['type'] ?? 'unknown_table';
        $canonicalKeys = $mappingConfig['canonical_keys'] ?? [];

        Log::info("OpenAI: Starting normalization for type '{$type}'", [
            'content_length' => strlen($content),
            'model' => $this->model,
        ]);

        $prompt = $this->buildPrompt($content, $type, $canonicalKeys);
        $startTime = microtime(true);

        try {
            $timeout = config('services.openai.timeout', (int) env('OPENAI_TIMEOUT', 60));
            Log::info("OpenAI: Sending request to API", [
                'prompt_length' => strlen($prompt),
                'timeout' => $timeout,
            ]);

            $response = Http::withToken($this->apiKey)
                ->timeout($timeout)
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a sports data parser. Your task is to extract structured data from HTML table fragments and normalize it into a specific JSON format.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1,
                ]);

            if ($response->failed()) {
                $duration = round(microtime(true) - $startTime, 2);
                Log::error('OpenAI Normalizer API Error', [
                    'status' => $response->status(),
                    'reason' => $response->reason(),
                    'duration' => $duration.'s',
                    'body' => $response->body(),
                ]);
                throw new Exception("OpenAI API request failed after {$duration}s: ".$response->reason());
            }

            $duration = round(microtime(true) - $startTime, 2);
            $result = $response->json();
            $contentResponse = $result['choices'][0]['message']['content'] ?? '';

            Log::info("OpenAI: Received response", [
                'duration' => $duration.'s',
                'response_length' => strlen($contentResponse),
                'finish_reason' => $result['choices'][0]['finish_reason'] ?? 'unknown',
            ]);

            $parsedData = json_decode($contentResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("OpenAI: JSON decode failed", [
                    'error' => json_last_error_msg(),
                    'raw_content' => substr($contentResponse, 0, 1000),
                ]);
                throw new Exception('Failed to parse JSON response from OpenAI.');
            }

            return $this->mapToDTO($parsedData, $type);

        } catch (Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            Log::error('OpenAI Normalizer Exception', [
                'message' => $e->getMessage(),
                'type' => $type,
                'duration' => $duration.'s',
                'content_length' => strlen($content),
                'timeout_config' => config('services.openai.timeout', (int) env('OPENAI_TIMEOUT', 60)),
            ]);
            throw $e;
        }
    }

    protected function buildPrompt(string $html, string $type, array $canonicalKeys): string
    {
        $keysList = implode(', ', array_keys($canonicalKeys));

        $structure = '{
     "name": "Descriptive name of the table",
     "columns": [{"key": "canonical_key", "label": "Original Label"}],
     "rows": [
       {
         "player_external_id": "ID from link /hrac/{id} or null",
         "player_name": "Full name",
         "values": {"canonical_key": value, ...},
         "row_label": "Optional label if player_name is missing"
       }
     ],
     "warnings": ["List of any parsing issues or missing values"]
   }';

        if ($type === 'match_boxscore') {
            $structure = '{
     "header": {
       "home_team": "Name of home team",
       "away_team": "Name of away team",
       "score": "Final score in format HH:AA",
       "date": "Date and time of match"
     },
     "tables": [
       {
         "name": "Team name (e.g. Sokol Kbely)",
         "columns": [{"key": "canonical_key", "label": "Original Label"}],
         "rows": [
           {
             "player_external_id": "ID from link /hrac/{id} or null",
             "player_name": "Full name",
             "values": {"canonical_key": value, ...}
           }
         ]
       }
     ],
     "warnings": ["List of any parsing issues"]
   }';
        }

        return <<<PROMPT
Parse the following HTML fragment into a structured JSON object.
Table type: $type
Target canonical keys (and their meanings): $keysList

Rules:
1. Output MUST be a valid JSON object.
2. Structure:
$structure
3. Mapping: Use only the provided canonical keys for the "values" object.
4. No Hallucinations: If a value is missing in HTML, set it to null and add a warning.
5. Numeric values: Ensure numbers are returned as integers/floats where appropriate.
6. Player IDs: Extract the numeric ID from any href containing "/hrac/{id}".
7. If multiple tables are present (like Home and Away boxscores), return them in the "tables" array.

HTML Fragment:
$html
PROMPT;
    }

    protected function mapToDTO(array $data, string $type): NormalizedTableDTO
    {
        if ($type === 'match_boxscore' && isset($data['tables'])) {
            // Special handling for multiple tables in boxscore
            $allTables = [];
            foreach ($data['tables'] as $table) {
                $rows = [];
                foreach ($table['rows'] ?? [] as $row) {
                    $rows[] = new NormalizedRowDTO(
                        values: $row['values'] ?? [],
                        playerId: null,
                        rowLabel: $row['player_name'] ?? $row['row_label'] ?? null,
                        metadata: [
                            'external_player_id' => $row['player_external_id'] ?? null,
                            'ai_normalized' => true,
                        ]
                    );
                }
                $allTables[] = new NormalizedTableDTO(
                    name: $table['name'] ?? $type,
                    columns: $table['columns'] ?? [],
                    rows: $rows,
                    metadata: ['ai_normalized' => true]
                );
            }

            $mainTable = $allTables[0] ?? new NormalizedTableDTO($type, [], [], ['warnings' => ['No tables found by AI']]);
            $mainTable->metadata = array_merge($mainTable->metadata, [
                'header' => $data['header'] ?? [],
                'all_tables' => array_map(fn ($t) => $t->toArray(), $allTables),
                'warnings' => $data['warnings'] ?? [],
                'source' => 'openai',
                'model' => $this->model,
            ]);

            return $mainTable;
        }

        $rows = [];
        foreach ($data['rows'] ?? [] as $row) {
            $rows[] = new NormalizedRowDTO(
                values: $row['values'] ?? [],
                playerId: null, // This is internall ID, we store external in metadata/rowLabel for now or map later
                rowLabel: $row['player_name'] ?? $row['row_label'] ?? null,
                metadata: [
                    'external_player_id' => $row['player_external_id'] ?? null,
                    'ai_normalized' => true,
                ]
            );
        }

        return new NormalizedTableDTO(
            name: $data['name'] ?? $type,
            columns: $data['columns'] ?? [],
            rows: $rows,
            metadata: [
                'warnings' => $data['warnings'] ?? [],
                'source' => 'openai',
                'model' => $this->model,
            ]
        );
    }
}
