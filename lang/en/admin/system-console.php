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
                'no_interaction' => 'Non-interactive',
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
        ],
        'stats_import' => [
            'label' => 'Statistics: Import',
            'desc' => 'Runs import of external match and player statistics.',
        ],
        'system_cleanup' => [
            'label' => 'System: Maintenance',
            'desc' => 'Performs system maintenance (clearing logs, etc.).',
        ],
        'audit_cleanup' => [
            'label' => 'Audit Log: Cleanup',
            'desc' => 'Removes old records from the audit log.',
            'flags' => [
                '30' => '30 days',
                '90' => '90 days',
                '180' => '180 days',
            ],
        ],
        'backfill_ids' => [
            'label' => 'Users: Fill IDs',
            'desc' => 'Fills missing club_member_id and payment_vs.',
            'flags' => [
                'regenerate' => 'Regenerate existing',
            ],
        ],
        'rsvp_reminders' => [
            'label' => 'RSVP: Reminders',
            'desc' => 'Sends reminders for unconfirmed attendance.',
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
        'optimize_clear' => [
            'label' => 'Optimize: Clear',
            'desc' => 'Clears all cached files (config, routes, views).',
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
            'label' => 'Storage: Link',
            'desc' => 'Creates symbolic link for storage folder (required for images).',
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
        'internal_tooltip' => 'Runs the command directly in the application\'s PHP process (Artisan::call) instead of calling the shell. Recommended if the PHP CLI binary fails in the shell.',
        'run' => 'Run',
        'working' => 'Working...',
    ],
];
