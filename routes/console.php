<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('seo:generate-sitemap')->dailyAt('03:00');

// Automatická obnova sezóny - 31. srpna ve 23:55
Schedule::command('season:renew')->cron('55 23 31 8 *');

// Automatické promazávání Telescope záznamů (zachová posledních 24 hodin)
Schedule::command('telescope:prune')->daily();
