<?php

namespace App\Services\Stats\Legacy;

use Illuminate\Support\Str;

class LegacyFileClassifier
{
    public function classify(string $filename, string $content): array
    {
        $detectedSeason = $this->detectSeason($filename, $content);
        $detectedTeam = $this->detectTeam($filename, $content);
        $fileType = $this->detectFileType($filename, $content);

        return [
            'season' => $detectedSeason,
            'team' => $detectedTeam,
            'file_type' => $fileType,
        ];
    }

    protected function detectSeason(string $filename, string $content): ?string
    {
        // Hledání v názvu souboru: 2015-2016, 2015-16, 2015_2016, 2015/2016, 2015/16
        if (preg_match('/(20\d{2})[-_\/](20\d{2}|(\d{2}))/', $filename, $matches)) {
            $year1 = $matches[1];
            $year2 = $matches[2];

            if (strlen($year2) === 2) {
                $year2 = substr($year1, 0, 2) . $year2;
            }

            return "{$year1}/{$year2}";
        }

        // Hledání v obsahu (např. v nadpisu)
        if (preg_match('/20\d{2}[-\/](20\d{2}|(\d{2}))/', $content, $matches)) {
            $year1 = $matches[1];
            $year2 = $matches[2];

            if (strlen($year2) === 2) {
                $year2 = substr($year1, 0, 2) . $year2;
            }

            return "{$year1}/{$year2}";
        }

        // Jen jeden rok
        if (preg_match('/(20\d{2})/', $filename, $matches)) {
            $year = $matches[1];
            return "{$year}/" . ($year + 1);
        }

        return null;
    }

    protected function detectTeam(string $filename, string $content): ?string
    {
        $search = Str::lower($filename . ' ' . strip_tags($content));

        if (Str::contains($search, ['muzi e', 'muži e', 'team e', 'tým e', 'kbely e'])) {
            return 'muzi-e';
        }

        if (Str::contains($search, ['muzi c', 'muži c', 'team c', 'tým c', 'kbely c'])) {
            return 'muzi-c';
        }

        if (Str::contains($search, ['muzi', 'muži'])) {
            return 'muzi'; // fallback
        }

        return null;
    }

    protected function detectFileType(string $filename, string $content): string
    {
        $search = Str::lower($filename . ' ' . strip_tags($content));

        if (Str::contains($search, ['hráči', 'hraci', 'soupiska', 'roster', 'hráčské statistiky'])) {
            return 'players_stats';
        }

        if (Str::contains($search, ['tabulka', 'konečná tabulka', 'league table', 'pořadí'])) {
            return 'league_table';
        }

        if (Str::contains($search, ['statistiky týmu', 'týmové statistiky', 'team statistics'])) {
            return 'team_stats';
        }

        return 'unknown';
    }
}
