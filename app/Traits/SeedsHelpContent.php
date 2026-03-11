<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait SeedsHelpContent
{
    /**
     * Upsert help record with protection for customized entries.
     *
     * @param string $modelClass The Eloquent model class
     * @param array $data Non-translatable data (must include 'slug')
     * @param array $translations Translatable data in format ['field' => ['cs' => '...', 'en' => '...']]
     * @param bool $force Force update even if customized
     * @return Model
     */
    protected function upsertHelpItem(string $modelClass, array $data, array $translations = [], bool $force = false): Model
    {
        $slug = $data['slug'];
        /** @var Model $record */
        $record = $modelClass::where('slug', $slug)->first();

        // Prepare full data for hash calculation
        $fullData = array_merge($data, $translations);
        unset($fullData['source_hash']);
        unset($fullData['is_customized']);

        $newHash = md5(serialize($fullData));

        if ($record) {
            // Check if customized
            if ($record->is_customized && !$force) {
                // Log::info("Help seeder: Skipping customized record '{$slug}' for model '{$modelClass}'");
                return $record;
            }

            // Check if hash matches
            if ($record->source_hash === $newHash && !$force) {
                return $record;
            }

            // Update
            $record->fill($data);
            foreach ($translations as $field => $locales) {
                foreach ($locales as $locale => $value) {
                    $record->setTranslation($field, $locale, $value);
                }
            }
            $record->source_hash = $newHash;
            $record->save();

            return $record;
        }

        // Create new
        /** @var Model $record */
        $record = new $modelClass();
        $record->fill($data);
        foreach ($translations as $field => $locales) {
            foreach ($locales as $locale => $value) {
                $record->setTranslation($field, $locale, $value);
            }
        }
        $record->source_hash = $newHash;
        $record->is_customized = false;
        $record->save();

        return $record;
    }
}
