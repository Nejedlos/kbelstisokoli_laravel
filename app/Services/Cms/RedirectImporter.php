<?php

namespace App\Services\Cms;

use App\Models\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RedirectImporter
{
    /**
     * Importuje přesměrování z pole dat.
     *
     * Formát dat:
     * [
     *   ['source' => '/stara-cesta', 'target' => '/nova-cesta', 'code' => 301],
     *   ...
     * ]
     */
    public function import(array $data, bool $overwrite = false): array
    {
        $results = [
            'success' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($data, $overwrite, &$results) {
            foreach ($data as $index => $row) {
                $validator = Validator::make($row, [
                    'source' => 'required|string',
                    'target' => 'required|string',
                    'code' => 'integer|in:301,302',
                ]);

                if ($validator->fails()) {
                    $results['errors'][] = "Řádek {$index}: ".implode(', ', $validator->errors()->all());

                    continue;
                }

                $source = '/'.ltrim($row['source'], '/');
                $target = $row['target'];
                $isExternal = str_starts_with($target, 'http');

                $existing = Redirect::where('source_path', $source)->first();

                if ($existing && ! $overwrite) {
                    $results['skipped']++;

                    continue;
                }

                Redirect::updateOrCreate(
                    ['source_path' => $source],
                    [
                        'target_type' => $isExternal ? 'external' : 'internal',
                        'target_path' => $isExternal ? null : '/'.ltrim($target, '/'),
                        'target_url' => $isExternal ? $target : null,
                        'status_code' => $row['code'] ?? 301,
                        'is_active' => true,
                        'match_type' => 'exact',
                        'notes' => 'Importováno z legacy systému',
                    ]
                );

                $results['success']++;
            }
        });

        return $results;
    }
}
