<?php

return [
    'groups' => [
        'ai' => '🧠 AI & Vyhledávání',
        'deploy' => '🚀 Správa & Nasazení',
        'sync' => '🔄 Synchronizace dat',
        'maintenance' => '🧹 Údržba & Čištění',
        'database' => '💾 Databáze',
        'optimization' => '⚡ Optimalizace & Cache',
        'dev_tools' => '🛠️ Vývojářské nástroje',
        'diagnostics' => '📊 Diagnostika',
        'legacy' => '💾 Data ze starého systému',
    ],
    'commands' => [
        'ai_index' => [
            'label' => 'AI: Hard Reindexace',
            'desc' => 'Kompletní sestavení vyhledávacího indexu z obsahu webu a administrace.',
            'flags' => [
                'all' => 'Všechny jazyky',
                'cs' => 'Pouze čeština',
                'en' => 'Pouze angličtina',
                'fresh' => 'Smazat index (Fresh)',
                'enrich' => 'Obohatit přes AI (Pomalé)',
                'no_interaction' => 'Neinteraktivně',
            ],
        ],
        'deploy' => [
            'label' => 'Produkce: Nasadit (Deploy)',
            'desc' => 'Spustí kompletní deployment proces na produkční server přes Envoy.',
        ],
        'sync' => [
            'label' => 'Produkce: Synchronizovat',
            'desc' => 'Spustí migrace a optimalizaci na produkci (pouze po manuálním nahrání souborů).',
        ],
        'local_prepare' => [
            'label' => 'Local: Příprava pro FTP',
            'desc' => 'Sestaví assety a připraví vše pro ruční nahrání na hosting přes FTP.',
        ],
        'prod_setup' => [
            'label' => 'Setup: Produkce',
            'desc' => 'Prvotní nastavení produkčního prostředí a nasazení.',
        ],
        'icons_sync' => [
            'label' => 'Ikony: Synchronizace',
            'desc' => 'Stáhne ikony Font Awesome Pro a vygeneruje cache pro aplikaci.',
            'flags' => [
                'pro' => 'Vynutit Pro verzi',
            ],
        ],
        'icons_doctor' => [
            'label' => 'Ikony: Diagnostika',
            'desc' => 'Zkontroluje integritu fontů a SVG ikon v projektu.',
        ],
        'announcements_sync' => [
            'label' => 'Oznámení: Sync',
            'desc' => 'Synchronizuje stav oznámení a deaktivuje expirovaná.',
        ],
        'finance_sync' => [
            'label' => 'Finance: Sync',
            'desc' => 'Synchronizuje platby a stavy účtů.',
            'flags' => [
                '--fresh' => 'Fresh (vymaže stará data + import)',
                '--import' => 'Importovat (z legacy DB)',
                '--force' => 'Vynutit (přeskočit potvrzení)',
            ],
        ],
        'finance_cleanup' => [
            'label' => 'Finance: Vyčistit',
            'desc' => 'SMAŽE veškerá finanční data (předpisy, platby). Pouze pro reset.',
            'flags' => [
                '--force' => 'Vynutit (přeskočit potvrzení)',
            ],
        ],
        'stats_import' => [
            'label' => 'Statistiky: Import',
            'desc' => 'Spustí import externích statistik zápasů a hráčů.',
        ],
        'system_cleanup' => [
            'label' => 'Systém: Údržba',
            'desc' => 'Provede systémovou údržbu (promazání logů apod.).',
        ],
        'audit_cleanup' => [
            'label' => 'Audit Log: Čištění',
            'desc' => 'Odstraní staré záznamy z audit logu.',
            'flags' => [
                '30' => '30 dní',
                '90' => '90 dní',
                '180' => '180 dní',
            ],
        ],
        'backfill_ids' => [
            'label' => 'Uživatelé: Doplnit ID',
            'desc' => 'Doplní chybějící club_member_id a payment_vs.',
            'flags' => [
                'regenerate' => 'Regenerovat i existující',
            ],
        ],
        'attendance_reminders' => [
            'label' => 'Docházka: Upomínky',
            'desc' => 'Odešle upomínky na nepotvrzenou docházku.',
        ],
        'migrate' => [
            'label' => 'Migrace (migrate)',
            'desc' => 'Spustí chybějící databázové migrace.',
            'flags' => [
                'force' => 'Vynutit v produkci',
                'seed' => 'Spustí seedy',
            ],
        ],
        'migrate_rollback' => [
            'label' => 'Vrátit migrace (rollback)',
            'desc' => 'Vrátí zpět poslední dávku migrací.',
            'flags' => [
                'force' => 'Vynutit',
                'step' => 'Krok 1',
            ],
        ],
        'db_seed' => [
            'label' => 'Spustit Seedy',
            'desc' => 'Naplní databázi testovacími nebo výchozími daty.',
            'select_label' => 'Vybrat Seeder',
            'flags' => [
                'force' => 'Vynutit',
            ],
        ],
        'app_seed' => [
            'label' => 'App: Seed',
            'desc' => 'Globální seedování s podporou fresh režimu.',
            'flags' => [
                'fresh' => 'Fresh mode',
            ],
        ],
        'optimize_clear' => [
            'label' => 'Optimize: Clear',
            'desc' => 'Vymaže veškeré zakešované soubory (config, routes, views).',
        ],
        'config_cache' => [
            'label' => 'Config: Cache',
            'desc' => 'Vytvoří cache soubor pro konfiguraci (rychlejší načítání).',
        ],
        'route_cache' => [
            'label' => 'Route: Cache',
            'desc' => 'Vytvoří cache soubor pro routy.',
        ],
        'view_cache' => [
            'label' => 'View: Cache',
            'desc' => 'Vytvoří cache soubor pro Blade šablony.',
        ],
        'storage_link' => [
            'label' => 'Storage: Link',
            'desc' => 'Vytvoří symbolický odkaz pro složku storage (nutné pro obrázky).',
        ],
        'npm_install' => [
            'label' => 'NPM: Install',
            'desc' => 'Nainstaluje závislosti (node_modules).',
        ],
        'npm_build' => [
            'label' => 'NPM: Run Build',
            'desc' => 'Sestaví assety (Vite) pro produkci.',
        ],
        'composer_install' => [
            'label' => 'Composer: Install',
            'desc' => 'Nainstaluje PHP závislosti (vendor).',
            'flags' => [
                'no_dev' => 'Bez dev balíčků',
                'optimize' => 'Optimalizovat',
            ],
        ],
        'git_status' => [
            'label' => 'Git: Status',
            'desc' => 'Zobrazí stav verzovacího systému.',
        ],
        'git_pull' => [
            'label' => 'Git: Pull',
            'desc' => 'Stáhne nejnovější změny z GitHubu.',
        ],
        'legacy_sync' => [
            'label' => 'Legacy: Kompletní Sync',
            'desc' => 'Kompletní synchronizace dat ze starého systému (členové, akce, docházka, finance).',
            'flags' => [
                '--fresh' => 'Fresh (smazat a znovu načíst)',
                '--users' => '⚠️ Sync uživatelů (může přepsat účty!)',
            ],
        ],
        'legacy_attendance_sync' => [
            'label' => 'Legacy: Jen docházka',
            'desc' => 'Samostatná synchronizace pouze pro docházku a související události.',
            'flags' => [
                '--fresh' => 'Fresh (smazat a znovu načíst)',
            ],
        ],
    ],
    'notifications' => [
        'completed' => 'Příkaz dokončen',
        'failed' => 'Příkaz selhal',
        'execution_error' => 'Chyba při spouštění',
    ],
    'actions' => [
        'system_check' => 'System Check',
    ],
    'ui' => [
        'internal_execution' => 'Internal Execution',
        'internal_tooltip' => 'Spustí příkaz přímo v PHP procesu aplikace (Artisan::call) místo volání shellu. Doporučeno, pokud selhává binárka PHP v shellu. Nedoporučuje se pro dlouhotrvající operace (timeout).',
        'run' => 'Spustit',
        'working' => 'Pracuji...',
    ],
];
