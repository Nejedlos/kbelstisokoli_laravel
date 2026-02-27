<?php

return [
    'navigation' => [
        'groups' => [
            'content' => 'Content',
            'media' => 'Media',
            'sports_agenda' => 'Sports Agenda',
            'statistics' => 'Statistics',
            'communication' => 'Communication',
            'user_management' => 'User Management',
            'finance' => 'Finance',
            'admin_tools' => 'Admin Tools',
        ],
        'pages' => [
            'dashboard' => 'Dashboard',
            'branding' => 'Branding & Appearance',
            'ai_settings' => 'AI Settings',
            'member_section' => 'Member Section',
            'public_web' => 'Public Website',
            'system_console' => 'System Console',
        ],
        'resources' => [
            'announcement' => [
                'label' => 'Announcement',
                'plural_label' => 'Announcements',
            ],
            'basketball_match' => [
                'label' => 'Match',
                'plural_label' => 'Matches',
            ],
            'club_competition' => [
                'label' => 'Club Competition',
                'plural_label' => 'Club Competitions',
            ],
            'club_event' => [
                'label' => 'Club Event',
                'plural_label' => 'Club Events',
            ],
            'cron_log' => [
                'label' => 'Cron Job Log',
                'plural_label' => 'Cron Logs',
            ],
            'cron_task' => [
                'label' => 'Cron Task',
                'plural_label' => 'Cron Tasks',
            ],
            'external_stat_source' => [
                'label' => 'External Source',
                'plural_label' => 'External Sources',
            ],
            'finance_charge' => [
                'label' => 'Finance Charge',
                'plural_label' => 'Finance Charges',
            ],
            'finance_payment' => [
                'label' => 'Payment',
                'plural_label' => 'Payments',
            ],
            'gallery' => [
                'label' => 'Gallery',
                'plural_label' => 'Galleries',
            ],
            'media_asset' => [
                'label' => 'Library Asset',
                'plural_label' => 'Media Library',
            ],
            'menu' => [
                'label' => 'Menu',
                'plural_label' => 'Menus',
            ],
            'opponent' => [
                'label' => 'Opponent',
                'plural_label' => 'Opponents',
            ],
            'page' => [
                'label' => 'Page',
                'plural_label' => 'Pages',
            ],
            'permission' => [
                'label' => 'Permission',
                'plural_label' => 'Permissions',
            ],
            'player_profile' => [
                'label' => 'Player Profile',
                'plural_label' => 'Player Profiles',
            ],
            'post' => [
                'label' => 'News Post',
                'plural_label' => 'News',
            ],
            'post_category' => [
                'label' => 'Category',
                'plural_label' => 'News Categories',
            ],
            'lead' => [
                'label' => 'Lead / Prospect',
                'plural_label' => 'Leads / Prospects',
            ],
            'audit_log' => [
                'label' => 'Audit Log',
                'plural_label' => 'Audit Logs',
            ],
            'redirect' => [
                'label' => 'Redirect',
                'plural_label' => 'Redirects',
            ],
            'role' => [
                'label' => 'Role',
                'plural_label' => 'Roles',
            ],
            'season' => [
                'label' => 'Season',
                'plural_label' => 'Seasons',
            ],
            'statistic_set' => [
                'label' => 'Statistic Set',
                'plural_label' => 'Statistic Sets',
            ],
            'team' => [
                'label' => 'Team',
                'plural_label' => 'Teams',
                'fields' => [
                    'name' => 'Team Name',
                    'slug' => 'Identifier (Slug)',
                    'category' => 'Category',
                    'coaches_count' => 'Coaches',
                    'players_count' => 'Players',
                    'description' => 'Team Description',
                    'coaches' => 'Coaches',
                    'players' => 'Players',
                    'coach_email' => 'Contact Email (for this team)',
                    'coach_email_help' => 'If filled, it will be displayed on the web instead of the coach\'s main email.',
                    'coach_phone' => 'Contact Phone (for this team)',
                    'coach_phone_help' => 'If filled, it will be displayed on the web instead of the coach\'s main phone.',
                    'role_in_team' => 'Role in team',
                    'is_primary_team' => 'Primary team',
                ],
                'tabs' => [
                    'general' => 'General Information',
                ],
                'actions' => [
                    'view_public' => 'View on Web',
                    'attach_coach' => 'Attach Coach',
                    'attach_player' => 'Attach Player',
                    'edit_coach_contact' => 'Edit contact info',
                    'detach' => 'Detach',
                    'detach_selected' => 'Detach selected',
                ],
            ],
            'training' => [
                'label' => 'Training',
                'plural_label' => 'Trainings',
            ],
            'user' => [
                'label' => 'User',
                'plural_label' => 'Users',
            ],
            'photo_pool' => [
                'label' => 'Photo Pool',
                'plural_label' => 'Photo Pools',
                'title' => 'Photo Gallery',
                'actions' => [
                    'create_wizard' => 'Add Gallery',
                    'regenerate_ai' => 'Improve with AI',
                    'detach' => 'Remove from pool',
                ],
                'notifications' => [
                    'ai_regenerated' => 'Metadata has been regenerated using AI.',
                ],
                'steps' => [
                    'context' => [
                        'label' => 'Base Context',
                        'description' => 'Enter information for AI analysis',
                    ],
                    'review' => [
                        'label' => 'AI Proposal & Review',
                        'description' => 'Check bilingual texts',
                    ],
                    'upload' => [
                        'label' => 'Photo Upload',
                        'description' => 'Bulk dropzone for your images',
                    ],
                ],
                'fields' => [
                    'preliminary_title' => 'Preliminary Title',
                    'preliminary_date' => 'Approximate Date',
                    'preliminary_description' => 'Short Description',
                    'event_date' => 'Event Date (normalized)',
                    'slug' => 'URL Identifier (slug)',
                    'title_cs' => 'Title (CS)',
                    'description_cs' => 'Description (CS)',
                    'title_en' => 'Title (EN)',
                    'description_en' => 'Description (EN)',
                    'photos' => 'Photos',
                ],
            ],
        ],
    ],
    'loader' => [
        'ai_thinking' => 'Assistant is thinking…',
    ],
    'search' => [
        'categories' => [
            'pages' => 'Pages',
            'resources' => 'Resources',
            'navigation' => 'Navigation',
            'other' => 'Other',
        ],
        'details' => [
            'group' => 'Menu Group',
            'content' => 'Content',
        ],
    ],
];
