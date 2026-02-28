<?php

namespace App\Filament\Resources\PerformanceTestResults\Widgets;

use App\Models\PerformanceTestResult;
use Filament\Widgets\Widget;

class PerformanceComparisonWidget extends Widget
{
    protected string $view = 'filament.resources.performance-test-results.widgets.performance-comparison-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // Získáme unikátní testované body (label + section)
        $testPoints = PerformanceTestResult::select('label', 'section')
            ->distinct()
            ->get();

        $comparison = [];

        foreach ($testPoints as $point) {
            $row = [
                'label' => $point->label,
                'section' => $point->section,
                'standard' => $this->getLatestResult($point->label, $point->section, 'standard'),
                'aggressive' => $this->getLatestResult($point->label, $point->section, 'aggressive'),
                'ultra' => $this->getLatestResult($point->label, $point->section, 'ultra'),
            ];

            // Vypočítáme úsporu oproti standardu
            if ($row['standard']) {
                if ($row['aggressive']) {
                    $row['aggressive_gain'] = round((1 - ($row['aggressive']->duration_ms / $row['standard']->duration_ms)) * 100, 1);
                }
                if ($row['ultra']) {
                    $row['ultra_gain'] = round((1 - ($row['ultra']->duration_ms / $row['standard']->duration_ms)) * 100, 1);
                }
            }

            $comparison[] = $row;
        }

        return [
            'comparison' => $comparison,
        ];
    }

    protected function getLatestResult(string $label, string $section, string $scenario)
    {
        return PerformanceTestResult::where('label', $label)
            ->where('section', $section)
            ->where('scenario', $scenario)
            ->latest()
            ->first();
    }
}
