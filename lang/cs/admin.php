<?php

return [
    'navigation' => [
        'groups' => [
            'content' => 'Obsah',
            'media' => 'Média',
            'sports_agenda' => 'Sportovní agenda',
            'statistics' => 'Statistiky',
            'communication' => 'Komunikace',
            'user_management' => 'Správa uživatelů',
            'finance' => 'Finance',
            'admin_tools' => 'Admin nástroje',
        ],
        'pages' => [
            'dashboard' => 'Nástěnka',
            'branding' => 'Branding a vzhled',
            'ai_settings' => 'AI Nastavení',
            'member_section' => 'Členská sekce',
            'public_web' => 'Veřejný web',
            'system_console' => 'Systémová konzole',
        ],
        'resources' => [
            'announcement' => [
                'label' => 'Oznámení',
                'plural_label' => 'Oznámení',
            ],
            'basketball_match' => [
                'label' => 'Zápas',
                'plural_label' => 'Zápasy',
            ],
            'club_competition' => [
                'label' => 'Klubová soutěž',
                'plural_label' => 'Klubové soutěže',
            ],
            'club_event' => [
                'label' => 'Klubová akce',
                'plural_label' => 'Klubové akce',
            ],
            'cron_log' => [
                'label' => 'Log plánované úlohy',
                'plural_label' => 'Logy cronu',
            ],
            'cron_task' => [
                'label' => 'Plánovaná úloha',
                'plural_label' => 'Plánované úlohy',
            ],
            'external_stat_source' => [
                'label' => 'Externí zdroj',
                'plural_label' => 'Externí zdroje',
            ],
            'finance_charge' => [
                'label' => 'Předpis platby',
                'plural_label' => 'Předpisy plateb',
            ],
            'finance_payment' => [
                'label' => 'Platba',
                'plural_label' => 'Platby',
            ],
            'gallery' => [
                'label' => 'Galerie',
                'plural_label' => 'Galerie',
            ],
            'media_asset' => [
                'label' => 'Asset v knihovně',
                'plural_label' => 'Knihovna médií',
            ],
            'menu' => [
                'label' => 'Menu',
                'plural_label' => 'Menu',
            ],
            'opponent' => [
                'label' => 'Soupeř',
                'plural_label' => 'Soupeři',
            ],
            'page' => [
                'label' => 'Stránka',
                'plural_label' => 'Stránky',
            ],
            'permission' => [
                'label' => 'Oprávnění',
                'plural_label' => 'Oprávnění',
            ],
            'player_profile' => [
                'label' => 'Hráčský profil',
                'plural_label' => 'Hráčské profily',
            ],
            'post' => [
                'label' => 'Novinka',
                'plural_label' => 'Novinky',
            ],
            'post_category' => [
                'label' => 'Kategorie',
                'plural_label' => 'Kategorie novinek',
            ],
            'lead' => [
                'label' => 'Lead / Zájemce',
                'plural_label' => 'Leady / Zájemci',
            ],
            'audit_log' => [
                'label' => 'Auditní log',
                'plural_label' => 'Auditní logy',
                'fields' => [
                    'occurred_at' => 'Čas události',
                    'category' => 'Kategorie',
                    'event_key' => 'Událost / Klíč',
                    'action' => 'Akce',
                    'severity' => 'Závažnost',
                    'actor' => 'Aktér',
                    'subject' => 'Předmět',
                    'changes' => 'Změny',
                    'source' => 'Zdroj',
                    'url' => 'URL adresa',
                    'ip_address' => 'IP adresa (anonym.)',
                    'user_agent' => 'Prohlížeč / Systém',
                    'metadata' => 'Metadata',
                ],
                'categories' => [
                    'auth' => 'Autentizace',
                    'admin_crud' => 'Administrace (CRUD)',
                    'lead' => 'Leady / Zájemci',
                    'settings' => 'Nastavení',
                    'content' => 'Obsah webu',
                    'system' => 'Systém',
                ],
                'actions' => [
                    'created' => 'Vytvořeno',
                    'updated' => 'Upraveno',
                    'deleted' => 'Smazáno',
                    'login' => 'Přihlášení',
                    'logout' => 'Odhlášení',
                    'password_reset' => 'Reset hesla',
                    'failed_login' => 'Nezdařené přihlášení',
                ],
                'severities' => [
                    'info' => 'Info',
                    'warning' => 'Varování',
                    'critical' => 'Kritické',
                ],
                'tabs' => [
                    'general' => 'Základní informace',
                    'actor_subject' => 'Aktér a Předmět',
                    'context' => 'Kontext požadavku',
                ],
            ],
            'not_found_log' => [
                'label' => '404 Log',
                'plural_label' => 'Detekce 404',
            ],
            'redirect' => [
                'label' => 'Přesměrování',
                'plural_label' => 'Přesměrování',
            ],
            'role' => [
                'label' => 'Role',
                'plural_label' => 'Role',
            ],
            'season' => [
                'label' => 'Sezóna',
                'plural_label' => 'Sezóny',
            ],
            'statistic_set' => [
                'label' => 'Sada statistik',
                'plural_label' => 'Sady statistik',
            ],
            'team' => [
                'label' => 'Tým',
                'plural_label' => 'Týmy',
                'fields' => [
                    'name' => 'Název týmu',
                    'slug' => 'Identifikátor (Slug)',
                    'category' => 'Kategorie',
                    'coaches_count' => 'Trenéři',
                    'players_count' => 'Hráči',
                    'description' => 'Popis týmu',
                    'coaches' => 'Trenéři',
                    'players' => 'Hráči',
                    'coach_email' => 'Kontaktní e-mail (pro tento tým)',
                    'coach_email_help' => 'Pokud je vyplněn, zobrazí se na webu místo hlavního e-mailu trenéra.',
                    'coach_phone' => 'Kontaktní telefon (pro tento tým)',
                    'coach_phone_help' => 'Pokud je vyplněn, zobrazí se na webu místo hlavního telefonu trenéra.',
                    'role_in_team' => 'Role v týmu',
                    'is_primary_team' => 'Primární tým',
                ],
                'tabs' => [
                    'general' => 'Základní informace',
                ],
                'actions' => [
                    'view_public' => 'Zobrazit na webu',
                    'attach_coach' => 'Přiřadit trenéra',
                    'attach_player' => 'Přiřadit hráče',
                    'edit_coach_contact' => 'Upravit kontaktní údaje',
                    'detach' => 'Odebrat',
                    'detach_selected' => 'Odebrat vybrané',
                ],
            ],
            'training' => [
                'label' => 'Trénink',
                'plural_label' => 'Tréninky',
            ],
            'user' => [
                'label' => 'Uživatel',
                'plural_label' => 'Uživatelé',
            ],
            'photo_pool' => [
                'label' => 'Pool fotografií',
                'plural_label' => 'Pooly fotografií',
                'title' => 'Galerie fotografií',
                'actions' => [
                    'create_wizard' => 'Přidat galerii',
                    'regenerate_ai' => 'Vylepšit pomocí AI',
                    'detach' => 'Odebrat z poolu',
                ],
                'notifications' => [
                    'ai_regenerated' => 'Metadata byla znovu vygenerována pomocí AI.',
                ],
                'steps' => [
                    'context' => [
                        'label' => 'Základní kontext',
                        'description' => 'Zadejte informace pro AI analýzu',
                    ],
                    'review' => [
                        'label' => 'AI Návrh & Revize',
                        'description' => 'Zkontrolujte bilingvní texty',
                    ],
                    'upload' => [
                        'label' => 'Nahrávání fotografií',
                        'description' => 'Hromadný dropzone pro vaše snímky',
                    ],
                ],
                'fields' => [
                    'preliminary_title' => 'Předběžný název',
                    'preliminary_date' => 'Přibližné datum',
                    'preliminary_description' => 'Stručný popis akce',
                    'event_date' => 'Datum akce (normalizováno)',
                    'slug' => 'URL identifikátor (slug)',
                    'title_cs' => 'Název (CS)',
                    'description_cs' => 'Popis (CS)',
                    'title_en' => 'Title (EN)',
                    'description_en' => 'Description (EN)',
                    'photos' => 'Fotografie',
                ],
            ],
        ],
    ],
    'loader' => [
        'ai_thinking' => 'Asistent přemýšlí…',
    ],
    'search' => [
        'categories' => [
            'pages' => 'Stránky',
            'resources' => 'Sekce (Resources)',
            'navigation' => 'Navigace',
            'other' => 'Ostatní',
        ],
        'details' => [
            'group' => 'Sekce v menu',
            'content' => 'Obsah',
        ],
    ],
];
