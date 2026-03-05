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

        $prompt = $this->buildPrompt($content, $type, $canonicalKeys);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(config('services.openai.timeout', (int) env('OPENAI_TIMEOUT', 90)))
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
                Log::error('OpenAI Normalizer API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('OpenAI API request failed: '.$response->reason());
            }

            $result = $response->json();
            $parsedData = json_decode($result['choices'][0]['message']['content'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to parse JSON response from OpenAI.');
            }

            return $this->mapToDTO($parsedData, $type);

        } catch (Exception $e) {
            Log::error('OpenAI Normalizer Exception', [
                'message' => $e->getMessage(),
                'type' => $type,
            ]);
            throw $e;
        }
    }

    protected function buildPrompt(string $html, string $type, array $canonicalKeys): string
    {
        $keysList = implode(', ', array_keys($canonicalKeys));

        return <<<PROMPT
Parse the following HTML fragment into a structured JSON object.
Table type: $type
Target canonical keys (and their meanings): $keysList

Rules:
1. Output MUST be a valid JSON object.
2. Structure:
   {
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
   }
3. Mapping: Use only the provided canonical keys for the "values" object.
4. No Hallucinations: If a value is missing in HTML, set it to null and add a warning.
5. Numeric values: Ensure numbers are returned as integers/floats where appropriate.
6. Player IDs: Extract the numeric ID from any href containing "/hrac/{id}".

HTML Fragment:
$html
PROMPT;
    }

    protected function mapToDTO(array $data, string $type): NormalizedTableDTO
    {
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
