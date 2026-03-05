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
        $strictSchema = $mappingConfig['strict_schema'] ?? null;

        $sanitizedContent = $this->sanitizeHtml($content);
        $sanitizedLength = strlen($sanitizedContent);

        $debugLogs = [];
        $debugLogs[] = "[" . date('H:i:s') . "] Normalization started. Original length: " . strlen($content);
        $debugLogs[] = "[" . date('H:i:s') . "] Sanitized length: " . $sanitizedLength;

        $prompt = $this->buildPrompt($sanitizedContent, $type, $canonicalKeys, $strictSchema);
        $promptLength = strlen($prompt);
        $startTime = microtime(true);

        try {
            $timeout = config('services.openai.timeout', (int) env('OPENAI_TIMEOUT', 60));
            $connectTimeout = 10; // 10s na navázání spojení

            $debugLogs[] = "[" . date('H:i:s') . "] Sending request to OpenAI API (Model: {$this->model}, Timeout: {$timeout}s)";

            $response = Http::withToken($this->apiKey)
                ->connectTimeout($connectTimeout)
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

            $duration = round(microtime(true) - $startTime, 2);
            $debugLogs[] = "[" . date('H:i:s') . "] Response received after {$duration}s. Status: " . $response->status();

            if ($response->failed()) {
                $errorMsg = $response->reason();
                if ($response->status() === 408 || str_contains($errorMsg, 'timed out')) {
                    $errorMsg = "OpenAI API Timeout after {$duration}s (Limit: {$timeout}s). Prompt: {$promptLength} chars.";
                }

                $debugLogs[] = "[" . date('H:i:s') . "] API Error: " . $errorMsg;

                Log::error('OpenAI Normalizer API Error', [
                    'status' => $response->status(),
                    'reason' => $response->reason(),
                    'duration' => $duration.'s',
                    'debug_logs' => $debugLogs,
                ]);

                throw new Exception("OpenAI API request failed: " . $errorMsg . "\nLogs:\n" . implode("\n", $debugLogs));
            }

            $result = $response->json();
            $contentResponse = $result['choices'][0]['message']['content'] ?? '';

            $debugLogs[] = "[" . date('H:i:s') . "] JSON received (" . strlen($contentResponse) . " chars). Parsing...";

            $parsedData = json_decode($contentResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $debugLogs[] = "[" . date('H:i:s') . "] JSON Parse Error: " . json_last_error_msg();
                throw new Exception('Failed to parse JSON response from OpenAI. Logs: ' . implode("\n", $debugLogs));
            }

            return $this->mapToDTO($parsedData, $type, [
                'prompt_length' => $promptLength,
                'sanitized_length' => $sanitizedLength,
                'debug_logs' => $debugLogs,
            ]);

        } catch (Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $timeoutConfig = config('services.openai.timeout', (int) env('OPENAI_TIMEOUT', 60));
            $msg = $e->getMessage();

            $debugLogs[] = "[" . date('H:i:s') . "] Exception caught: " . $msg;

            if (str_contains($msg, 'timed out') || str_contains($msg, 'cURL error 28')) {
                $msg = "Timeout Error: OpenAI request took {$duration}s (Limit: {$timeoutConfig}s). Content size: " . strlen($content) . " chars. Type: {$type}";
            }

            Log::error('OpenAI Normalizer Exception', [
                'message' => $e->getMessage(),
                'type' => $type,
                'duration' => $duration.'s',
                'debug_logs' => $debugLogs,
            ]);

            throw new Exception($msg . "\n\nDebug Logs:\n" . implode("\n", $debugLogs));
        }
    }

    protected function buildPrompt(string $html, string $type, array $canonicalKeys, ?string $strictSchema = null): string
    {
        $keysList = implode(', ', array_keys($canonicalKeys));

        $structure = $strictSchema;

        if (!$structure) {
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

    public function sanitizeHtml(string $html): string
    {
        // Ponechat pouze obsah <body> pokud existuje
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Odebrat komentáře
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);

        // Odebrat skripty a styly
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        // Odebrat SVG
        $html = preg_replace('/<svg\b[^>]*>(.*?)<\/svg>/is', '', $html);

        // Odebrat nav, footer, header, head (pokud tam zbyly)
        $html = preg_replace('/<(nav|footer|header|head)\b[^>]*>(.*?)<\/\1>/is', '', $html);

        // Agresivní odstranění VŠECH atributů kromě povolených (href, colspan, rowspan)
        // Použijeme regex callback pro maximální spolehlivost
        $html = preg_replace_callback('/<([a-z0-9]+)(\s+[^>]*)?>/i', function($matches) {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2] ?? '';

            if (empty(trim($attrs))) {
                return "<{$tag}>";
            }

            $allowedAttrs = ['href', 'colspan', 'rowspan'];
            $cleanAttrs = '';

            foreach ($allowedAttrs as $attr) {
                // Najdeme atribut a jeho hodnotu (v uvozovkách i bez)
                if (preg_match('/' . $attr . '=(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attrs, $attrMatch)) {
                    $val = $attrMatch[1] ?: ($attrMatch[2] ?: $attrMatch[3]);
                    $cleanAttrs .= " {$attr}=\"{$val}\"";
                }
            }

            return "<{$tag}{$cleanAttrs}>";
        }, $html);

        // Odstranění všech tagů kromě strukturálních a odkazů
        // Ponecháme: table, thead, tbody, tfoot, tr, td, th, a, h1, h2, h3, h4, span, div, b, strong
        $html = strip_tags($html, '<table><thead><tbody><tfoot><tr><td><th><a><h1><h2><h3><h4><span><div><b><strong>');

        // Odebrat přebytečné mezery a konce řádků
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }

    protected function mapToDTO(array $data, string $type, array $additionalMetadata = []): NormalizedTableDTO
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
            ], $additionalMetadata);

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
            metadata: array_merge([
                'warnings' => $data['warnings'] ?? [],
                'source' => 'openai',
                'model' => $this->model,
            ], $additionalMetadata)
        );
    }
}
