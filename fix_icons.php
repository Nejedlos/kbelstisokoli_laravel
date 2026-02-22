<?php

$replacements = [
    'fal_users' => 'USERS',
    'fal_user_shield' => 'ROLES',
    'fal_key' => 'PERMISSIONS',
    'fal_id_card' => 'PLAYER_PROFILES',
    'fal_file_lines' => 'PAGES',
    'fal_newspaper' => 'POSTS',
    'fal_tags' => 'CATEGORIES',
    'fal_bars' => 'MENUS',
    'fal_basketball' => 'TRAININGS',
    'fal_basketball_hoop' => 'MATCHES',
    'fal_calendar_star' => 'EVENTS',
    'fal_calendar_days' => 'SEASONS',
    'fal_user_group' => 'TEAMS',
    'fal_trophy' => 'COMPETITIONS',
    'fal_shield' => 'OPPONENTS',
    'fal_cloud_arrow_down' => 'STAT_SOURCES',
    'fal_chart_column' => 'STAT_SETS',
    'fal_file_invoice_dollar' => 'FINANCE_CHARGES',
    'fal_money_bill_transfer' => 'FINANCE_PAYMENTS',
    'fal_images' => 'MEDIA_LIBRARY',
    'fal_film' => 'GALLERIES',
    'fal_announcement' => 'ANNOUNCEMENTS',
    'fal_clipboard_list' => 'AUDIT_LOGS',
    'fal_shuffle' => 'REDIRECTS',
    'fal_clock' => 'CRON_TASKS',
    'fal_history' => 'CRON_LOGS',
    'fal_palette' => 'BRANDING',
];

$dir = new RecursiveDirectoryIterator(__DIR__ . '/app/Filament');
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        foreach ($replacements as $old => $new) {
            $search = "return '$old';";
            $replace = "return \App\Support\FilamentIcon::get(\App\Support\FilamentIcon::$new);";
            $content = str_replace($search, $replace, $content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: $path\n";
        }
    }
}
