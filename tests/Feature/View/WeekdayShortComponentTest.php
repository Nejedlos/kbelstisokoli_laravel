<?php

namespace Tests\Feature\View;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class WeekdayShortComponentTest extends TestCase
{
    public function test_it_renders_a_localized_short_weekday_name(): void
    {
        $date = CarbonImmutable::create(2026, 9, 7); // Monday

        app()->setLocale('cs');
        $this->assertStringContainsString('Po', Blade::render('<x-weekday-short :date="$date" />', compact('date')));

        app()->setLocale('en');
        $this->assertStringContainsString('Mon', Blade::render('<x-weekday-short :date="$date" />', compact('date')));
    }
}
