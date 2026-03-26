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
                'no_ai' => 'Jen standardní hledání (bez AI)',
                'no_interaction' => 'Neinteraktivně',
                'force' => 'Vynutit (Force refresh)',
                'section_frontend' => 'Sekce: Frontend (Web)',
                'section_member' => 'Sekce: Member (Členská)',
                'section_admin' => 'Sekce: Admin (Správa)',
                'section_documentation' => 'Sekce: Dokumentace',
                'section_help' => 'Sekce: Nápověda',
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
        'finance_archive' => [
            'label' => 'Finance: Archivace',
            'desc' => 'Označí staré neuzavřené předpisy z předchozích sezón jako zaplacené.',
            'flags' => [
                '--dry-run' => 'Zkušební běh (bez změn)',
            ],
        ],
        'stats_import' => [
            'label' => 'Statistiky: Hromadný Import',
            'desc' => 'Spustí hromadnou synchronizaci všech týmů (automatizovaná pipeline).',
            'flags' => [
                'recent' => 'Pouze nedávné (Recent)',
            ],
        ],
        'stats_sync_players' => [
            'label' => 'Statistiky: Hráči (Sync)',
            'desc' => 'Synchronizace konkrétního hráče nebo všech členů klubu.',
            'input_label' => 'ID Uživatele (volitelné)',
            'team_filter_label' => 'Tým (filtr)',
            'all_teams' => '--- Všechny týmy ---',
            'flags' => [
                'excesive' => 'Excesivní režim (Historie)',
                'force' => 'Vynutit refresh (Ignore cache)',
            ],
        ],
        'stats_sync_standings' => [
            'label' => 'Statistiky: Tabulky (Sync)',
            'desc' => 'Synchronizace ligových tabulek (pořadí týmů) ze všech soutěží.',
            'flags' => [
                'force' => 'Vynutit (ignorovat cache)',
            ],
            'selects' => [
                'season' => [
                    'label' => 'Sezóna',
                    'active' => '--- Aktuální sezóna ---',
                    'all' => '--- Všechny sezóny ---',
                ],
            ],
        ],
        'stats_sync_team' => [
            'label' => 'Statistiky: Tým (Sync)',
            'desc' => 'Synchronizace kompletní soupisky a zápasů vybraného týmu.',
            'flags' => [
                'excesive' => 'Excesivní režim (Všechny boxscory)',
                'sync' => 'Synchronní běh (počkejte na dokončení)',
                'force' => 'Vynutit (ignorovat cache)',
            ],
            'selects' => [
                'team' => [
                    'label' => 'Výběr týmu',
                    'all' => '--- Všechny týmy ---',
                ],
                'season' => [
                    'label' => 'Výběr sezóny',
                    'all' => '--- Všechny sezóny ---',
                ],
            ],
        ],
        'stats_predictions_recompute' => [
            'label' => 'Statistiky: Předpovědi (Přepočet)',
            'desc' => 'Přepočítá pravděpodobnosti výsledků pro nastávající zápasy.',
            'flags' => [
                '--all' => 'Přepočítat i minulost (všechny zápasy)',
            ],
        ],
        'system_cleanup' => [
            'label' => 'Systém: Údržba',
            'desc' => 'Maže staré systémové protokoly (cron logy) starší než 30 dní.',
        ],
        'audit_cleanup' => [
            'label' => 'Audit Log: Čištění',
            'desc' => 'Odstraní staré záznamy z historie aktivit (audit logu) podle zvoleného počtu dní.',
            'flags' => [
                '30' => '30 dní',
                '90' => '90 dní',
                '180' => '180 dní',
            ],
        ],
        'backfill_ids' => [
            'label' => 'Uživatelé: Doplnit ID',
            'desc' => 'Vygeneruje chybějící unikátní ID členů a variabilní symboly pro platby u všech uživatelů.',
            'flags' => [
                'regenerate' => 'Regenerovat i existující',
            ],
        ],
        'rsvp_reminders' => [
            'label' => 'Docházka: Upomínky',
            'desc' => 'Odešle upozornění hráčům a trenérům, kteří nepotvrdili účast na akcích začínajících v příštích 24 hodinách.',
        ],
        'telescope_clear' => [
            'label' => 'Telescope: Vyčistit',
            'desc' => 'Smaže staré záznamy z Laravel Telescope (ponechá posledních 24 hodin).',
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
        'optimize' => [
            'label' => 'Optimize: All',
            'desc' => 'Komplexní optimalizace: config, routes, views a také PRIMING page cache.',
        ],
        'optimize_cache' => [
            'label' => 'Optimize: Cache',
            'desc' => 'Vytvoří cache soubory pro konfiguraci a routy (standardní Laravel).',
        ],
        'optimize_clear' => [
            'label' => 'Optimize: Clear',
            'desc' => 'Vymaže veškeré zakešované soubory (config, routes, views, page-cache).',
        ],
        'page_cache_prime' => [
            'label' => 'Page Cache: Prime',
            'desc' => 'Projde veřejné stránky webu a naplní full-page cache pro hosty.',
        ],
        'page_cache_clear' => [
            'label' => 'Page Cache: Clear',
            'desc' => 'Selektivně smaže pouze full-page cache (pokud to driver podporuje).',
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
            'label' => 'Storage: Link (Standard)',
            'desc' => 'Vytvoří symlink ze storage/app/public do veřejného adresáře (standardní Laravel způsob).',
        ],
        'storage_repair' => [
            'label' => 'Storage: Oprava (Webglobe)',
            'desc' => 'Vynutí vytvoření symlinku v REÁLNÉM veřejném adresáři (pro hosting Webglobe). Také zkontroluje složku uploads.',
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
    'diagnostics' => [
        'kpi' => [
            'artisan_processes' => 'Artisan procesy',
            'stuck_imports' => 'Zaseknuté importy',
            'table_cleanup' => 'Tabulky k vyčištění',
            'running' => 'Běžící',
            'stuck' => 'Zaseknutý',
            'stale' => 'Neaktivní',
            'stuck_desc' => 'Procesy běžící déle než 30 minut.',
            'stale_desc' => 'Importy v stavu "running" bez aktualizace.',
            'cleanup_desc' => 'Tabulky s velkým počtem záznamů.',
            'kill' => 'Ukončit',
            'fix' => 'Opravit',
            'prune' => 'Vyčistit',
            'no_issues' => 'Žádné kritické problémy nenalezeny.',
            'actions' => [
                'process_killed' => 'Proces :pid byl ukončen.',
                'process_kill_failed' => 'Nepodařilo se ukončit proces :pid.',
                'import_fixed' => 'Import :id byl označen jako selhaný.',
                'table_pruned' => 'Tabulka :table byla vyčištěna.',
                'bulk_imports_fixed' => 'Bylo opraveno :count zaseknutých importů.',
                'bulk_processes_killed' => 'Bylo ukončeno :count Artisan procesů.',
            ],
            'bulk' => [
                'fix_all' => 'Opravit vše',
                'kill_all' => 'Ukončit vše',
            ],
        ],
    ],
];
