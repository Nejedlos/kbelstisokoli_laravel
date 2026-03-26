<?php

return [
    'groups' => [
        'ai' => '🧠 AI & Search',
        'deploy' => '🚀 Management & Deployment',
        'sync' => '🔄 Data Synchronization',
        'maintenance' => '🧹 Maintenance & Cleanup',
        'database' => '💾 Database',
        'optimization' => '⚡ Optimization & Cache',
        'dev_tools' => '🛠️ Developer Tools',
        'diagnostics' => '📊 Diagnostics',
        'legacy' => '💾 Legacy Data',
    ],
    'commands' => [
        'ai_index' => [
            'label' => 'AI: Hard Reindex',
            'desc' => 'Complete rebuild of the search index from the website and administration content.',
            'flags' => [
                'all' => 'All languages',
                'cs' => 'Czech only',
                'en' => 'English only',
                'fresh' => 'Delete index (Fresh)',
                'enrich' => 'Enrich via AI (Slow)',
                'no_ai' => 'Standard search only (No AI)',
                'no_interaction' => 'Non-interactive',
                'force' => 'Force (Full refresh)',
                'section_frontend' => 'Section: Frontend (Web)',
                'section_member' => 'Section: Member (Private)',
                'section_admin' => 'Section: Admin (Management)',
                'section_documentation' => 'Section: Documentation',
                'section_help' => 'Section: Help',
            ],
        ],
        'deploy' => [
            'label' => 'Production: Deploy',
            'desc' => 'Runs full deployment process to production server via Envoy.',
        ],
        'sync' => [
            'label' => 'Production: Synchronize',
            'desc' => 'Runs migrations and optimization on production (only after manual file upload).',
        ],
        'local_prepare' => [
            'label' => 'Local: Prepare for FTP',
            'desc' => 'Builds assets and prepares everything for manual upload to hosting via FTP.',
        ],
        'prod_setup' => [
            'label' => 'Setup: Production',
            'desc' => 'Initial setup of production environment and deployment.',
        ],
        'icons_sync' => [
            'label' => 'Icons: Synchronization',
            'desc' => 'Downloads Font Awesome Pro icons and generates cache for the app.',
            'flags' => [
                'pro' => 'Force Pro version',
            ],
        ],
        'icons_doctor' => [
            'label' => 'Icons: Diagnostics',
            'desc' => 'Checks integrity of fonts and SVG icons in the project.',
        ],
        'announcements_sync' => [
            'label' => 'Announcements: Sync',
            'desc' => 'Synchronizes announcement status and deactivates expired ones.',
        ],
        'finance_sync' => [
            'label' => 'Finance: Sync',
            'desc' => 'Synchronizes payments and account balances.',
            'flags' => [
                '--fresh' => 'Fresh (clears old data + import)',
                '--import' => 'Import (from legacy DB)',
                '--force' => 'Force (skip confirmation)',
            ],
        ],
        'finance_cleanup' => [
            'label' => 'Finance: Cleanup',
            'desc' => 'DELETES all financial data (charges, payments). Reset only.',
            'flags' => [
                '--force' => 'Force (skip confirmation)',
            ],
        ],
        'finance_archive' => [
            'label' => 'Finance: Archive',
            'desc' => 'Marks old unclosed charges from previous seasons as paid.',
            'flags' => [
                '--dry-run' => 'Dry run (no changes)',
            ],
        ],
        'stats_import' => [
            'label' => 'Statistics: Bulk Import',
            'desc' => 'Runs bulk synchronization for all teams (automated pipeline).',
            'flags' => [
                'recent' => 'Recent only',
            ],
        ],
        'stats_sync_players' => [
            'label' => 'Statistics: Players (Sync)',
            'desc' => 'Synchronization of a specific player or all club members.',
            'input_label' => 'User ID (optional)',
            'team_filter_label' => 'Team (filter)',
            'all_teams' => '--- All Teams ---',
            'flags' => [
                'excesive' => 'Excesive mode (History)',
                'force' => 'Force refresh (Ignore cache)',
            ],
        ],
        'stats_sync_standings' => [
            'label' => 'Stats: Standings (Sync)',
            'desc' => 'Synchronize league standings (team ranking) from all competitions.',
            'flags' => [
                'force' => 'Force (ignore cache)',
            ],
            'selects' => [
                'season' => [
                    'label' => 'Season',
                    'active' => '--- Current Season ---',
                    'all' => '--- All Seasons ---',
                ],
            ],
        ],
        'stats_sync_team' => [
            'label' => 'Statistics: Team (Sync)',
            'desc' => 'Synchronization of the complete roster and matches for a selected team.',
            'flags' => [
                'excesive' => 'Excesive mode (All boxscores)',
                'sync' => 'Synchronous run (wait for completion)',
                'force' => 'Force refresh (Ignore cache)',
            ],
            'selects' => [
                'team' => [
                    'label' => 'Select Team',
                    'all' => '--- All Teams ---',
                ],
                'season' => [
                    'label' => 'Select Season',
                    'all' => '--- All Seasons ---',
                ],
            ],
        ],
        'system_cleanup' => [
            'label' => 'System: Maintenance',
            'desc' => 'Clears old system logs (cron logs) older than 30 days.',
        ],
        'audit_cleanup' => [
            'label' => 'Audit Log: Cleanup',
            'desc' => 'Removes old records from the activity history (audit log) based on the selected number of days.',
            'flags' => [
                '30' => '30 days',
                '90' => '90 days',
                '180' => '180 days',
            ],
        ],
        'backfill_ids' => [
            'label' => 'Users: Fill IDs',
            'desc' => 'Generates missing unique member IDs and variable symbols for payments for all users.',
            'flags' => [
                'regenerate' => 'Regenerate existing',
            ],
        ],
        'rsvp_reminders' => [
            'label' => 'RSVP: Reminders',
            'desc' => 'Sends notifications to players and coaches who haven\'t confirmed attendance for events starting in the next 24 hours.',
        ],
        'telescope_clear' => [
            'label' => 'Telescope: Clear',
            'desc' => 'Clears old records from Laravel Telescope (keeps last 24 hours).',
        ],
        'migrate' => [
            'label' => 'Migration (migrate)',
            'desc' => 'Runs missing database migrations.',
            'flags' => [
                'force' => 'Force in production',
                'seed' => 'Run seeds',
            ],
        ],
        'migrate_rollback' => [
            'label' => 'Rollback migrations',
            'desc' => 'Reverts the last batch of migrations.',
            'flags' => [
                'force' => 'Force',
                'step' => 'Step 1',
            ],
        ],
        'db_seed' => [
            'label' => 'Run Seeds',
            'desc' => 'Populates database with test or default data.',
            'select_label' => 'Select Seeder',
            'flags' => [
                'force' => 'Force',
            ],
        ],
        'app_seed' => [
            'label' => 'App: Seed',
            'desc' => 'Global seeding with fresh mode support.',
            'flags' => [
                'fresh' => 'Fresh mode',
            ],
        ],
        'optimize' => [
            'label' => 'Optimize: All',
            'desc' => 'Comprehensive optimization: config, routes, views, and also PRIMING page cache.',
        ],
        'optimize_cache' => [
            'label' => 'Optimize: Cache',
            'desc' => 'Creates cache files for configuration and routes (standard Laravel).',
        ],
        'optimize_clear' => [
            'label' => 'Optimize: Clear',
            'desc' => 'Clears all cached files (config, routes, views, page-cache).',
        ],
        'page_cache_prime' => [
            'label' => 'Page Cache: Prime',
            'desc' => 'Crawls public website pages and populates the full-page cache for guests.',
        ],
        'page_cache_clear' => [
            'label' => 'Page Cache: Clear',
            'desc' => 'Selectively clears only the full-page cache (if supported by driver).',
        ],
        'config_cache' => [
            'label' => 'Config: Cache',
            'desc' => 'Creates cache file for configuration (faster loading).',
        ],
        'route_cache' => [
            'label' => 'Route: Cache',
            'desc' => 'Creates cache file for routes.',
        ],
        'view_cache' => [
            'label' => 'View: Cache',
            'desc' => 'Creates cache file for Blade templates.',
        ],
        'storage_link' => [
            'label' => 'Storage: Link (Standard)',
            'desc' => 'Creates a symlink from storage/app/public to the public directory (standard Laravel way).',
        ],
        'storage_repair' => [
            'label' => 'Storage: Repair (Webglobe)',
            'desc' => 'Forces the creation of a symlink in the REAL public directory (for Webglobe hosting). Also checks the uploads folder.',
        ],
        'npm_install' => [
            'label' => 'NPM: Install',
            'desc' => 'Installs dependencies (node_modules).',
        ],
        'npm_build' => [
            'label' => 'NPM: Run Build',
            'desc' => 'Builds assets (Vite) for production.',
        ],
        'composer_install' => [
            'label' => 'Composer: Install',
            'desc' => 'Installs PHP dependencies (vendor).',
            'flags' => [
                'no_dev' => 'Without dev packages',
                'optimize' => 'Optimize',
            ],
        ],
        'git_status' => [
            'label' => 'Git: Status',
            'desc' => 'Shows the status of the version control system.',
        ],
        'git_pull' => [
            'label' => 'Git: Pull',
            'desc' => 'Downloads the latest changes from GitHub.',
        ],
        'legacy_sync' => [
            'label' => 'Legacy: Full Sync',
            'desc' => 'Complete data synchronization from the old system (members, events, attendance, finance).',
            'flags' => [
                '--fresh' => 'Fresh (delete and reload)',
                '--users' => '⚠️ User sync (may overwrite accounts!)',
            ],
        ],
        'legacy_attendance_sync' => [
            'label' => 'Legacy: Attendance only',
            'desc' => 'Separate synchronization only for attendance and related events.',
            'flags' => [
                '--fresh' => 'Fresh (delete and reload)',
            ],
        ],
    ],
    'notifications' => [
        'completed' => 'Command completed',
        'failed' => 'Command failed',
        'execution_error' => 'Execution error',
    ],
    'actions' => [
        'system_check' => 'System Check',
    ],
    'ui' => [
        'internal_execution' => 'Internal Execution',
        'internal_tooltip' => 'Runs the command directly in the application\'s PHP process (Artisan::call) instead of calling the shell. Recommended if the PHP CLI binary fails in the shell. Not recommended for long-running operations (timeout).',
        'run' => 'Run',
        'working' => 'Working...',
    ],
    'diagnostics' => [
        'kpi' => [
            'artisan_processes' => 'Artisan Processes',
            'stuck_imports' => 'Stuck Imports',
            'table_cleanup' => 'Table Cleanup',
            'running' => 'Running',
            'stuck' => 'Stuck',
            'stale' => 'Stale',
            'stuck_desc' => 'Processes running for more than 30 minutes.',
            'stale_desc' => 'Imports in "running" state without update.',
            'cleanup_desc' => 'Tables with high record counts.',
            'kill' => 'Kill',
            'fix' => 'Fix',
            'prune' => 'Prune',
            'no_issues' => 'No critical issues found.',
            'actions' => [
                'process_killed' => 'Process :pid was killed.',
                'process_kill_failed' => 'Failed to kill process :pid.',
                'import_fixed' => 'Import :id was marked as failed.',
                'table_pruned' => 'Table :table was pruned.',
                'bulk_imports_fixed' => ':count stuck imports were fixed.',
                'bulk_processes_killed' => ':count Artisan processes were killed.',
            ],
            'bulk' => [
                'fix_all' => 'Fix all',
                'kill_all' => 'Kill all',
            ],
        ],
    ],
];
