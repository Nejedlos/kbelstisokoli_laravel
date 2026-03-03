<?php

namespace App\Services\Stats\Legacy;

use Illuminate\Support\Str;

class LegacyFileClassifier
{
    public function classify(string $filename, string $content): array
    {
        $encoding = $this->detectEncoding($filename, $content);

        // Pokud je to Windows-1250, musíme content překódovat pro další analýzu
        if ($encoding === 'Windows-1250') {
            $content = iconv('Windows-1250', 'UTF-8//IGNORE', $content);
        }

        $detectedSeason = $this->detectSeason($filename, $content);
        $detectedTeam = $this->detectTeam($filename, $content);
        $fileType = $this->detectFileType($filename, $content);

        return [
            'season' => $detectedSeason,
            'team' => $detectedTeam,
            'file_type' => $fileType,
            'encoding' => $encoding,
        ];
    }

    protected function detectEncoding(string $filename, string $content): string
    {
        // Specifické soubory, o kterých víme, že jsou ve Windows-1250
        if (Str::contains($filename, 'sokoli_statistiky_')) {
            return 'Windows-1250';
        }

        if (preg_match('/charset=windows-1250/i', $content)) {
            return 'Windows-1250';
        }

        return 'UTF-8';
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

        // Hledání v obsahu (např. v nadpisu "ročník 2019/20")
        if (preg_match('/ročník\s+(20\d{2})[-\/](\d{2})/i', $content, $matches)) {
            return "{$matches[1]}/20{$matches[2]}";
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

        if (Str::contains($filename, 'sokoli_statistiky_')) {
            return 'mixed';
        }

        if (Str::contains($filename, 'konecna_tabulka_')) {
            return 'league_table';
        }

        if (Str::contains($filename, '_hraci')) {
            return 'players_stats';
        }

        if (Str::contains($filename, '_druzstvo')) {
            return 'team_stats';
        }

        // Fallback na obsah
        if (Str::contains($search, ['tabulka', 'konečná tabulka', 'league table', 'pořadí'])) {
            return 'league_table';
        }

        if (Str::contains($search, ['hráči', 'hraci', 'soupiska', 'roster', 'hráčské statistiky'])) {
            return 'players_stats';
        }

        if (Str::contains($search, ['statistiky týmu', 'týmové statistiky', 'team statistics'])) {
            return 'team_stats';
        }

        return 'unknown';
    }
}
