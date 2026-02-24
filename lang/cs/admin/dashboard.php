<?php

return [
    'welcome' => [
        'title' => 'Vítejte zpět, :name!',
        'text' => 'V klubu je aktuálně :active_players aktivních hráčů a systém běží bez chyb.',
        'quick_actions' => [
            'new_match' => 'Nový zápas',
            'new_match_hint' => 'Vytvořit nový zápas do kalendáře',
            'new_user' => 'Nový člen',
            'new_user_hint' => 'Registrovat nového člena nebo trenéra',
            'new_post' => 'Napsat novinku',
            'new_post_hint' => 'Publikovat článek na web a do aplikace',
            'new_training' => 'Nový trénink',
            'new_training_hint' => 'Naplánovat tréninkovou jednotku',
            'new_event' => 'Nová akce',
            'new_event_hint' => 'Vytvořit klubovou akci nebo kemp',
            'media_upload' => 'Multimédia',
            'media_upload_hint' => 'Správa fotografií a videí v galerii',
            'audit_log' => 'Auditní log',
            'audit_log_hint' => 'Prohlížet historii změn v systému',
            'finance' => 'Finance',
            'finance_hint' => 'Přehled plateb a členských příspěvků',
        ],
    ],

    'club_health' => [
        'title' => 'Stav oddílu',
    ],

    'contact_admin' => [
        'title' => 'Potřebujete pomoc od administrátora?',
        'text' => 'S technickými problémy nebo nastavením systému se prosím obracejte přímo na administrátora. Trenéři pracují v administraci, ale správu systému garantuje admin.',
        'cta' => 'Napsat administrátorovi',
        'mailto' => 'Odeslat e‑mail',
    ],

    'system' => [
        'title' => 'Stav systému',
        'cron' => [
            'label' => 'Plánovač úloh (Cron)',
            'ok' => 'V pořádku',
            'problem' => 'Problém',
            'last_run' => 'Naposledy: :time',
            'unknown' => 'Neznámé',
        ],
        'ai' => [
            'label' => 'AI index',
            'ready' => 'Připraveno',
            'needs_index' => 'Vyžaduje indexaci',
        ],
    ],

    'recent_activity' => [
        'title' => 'Poslední aktivity',
        'empty' => 'Žádné záznamy k zobrazení.',
        'actor_system' => 'Systém',
    ],

    'kpi' => [
        'users_total' => 'Uživatelé (celkem)',
        'users_active_desc' => 'Aktivních: :count',
        'players_total' => 'Hráčské profily',
        'teams_total' => 'Týmy',
        'matches_total' => 'Zápasy',
        'matches_upcoming_desc' => 'Nadcházející: :count',
        'trainings_total' => 'Tréninky',
        'trainings_upcoming_desc' => 'Nadcházející: :count',
        'attendance_total' => 'RSVP/Docházka',
        'attendance_desc' => 'Počet záznamů',
    ],

    'finance' => [
        'total_receivables' => 'Pohledávky celkem',
        'total_receivables_desc' => 'Otevřené a částečně zaplacené předpisy',
        'overdue' => 'Po splatnosti',
        'overdue_desc' => 'Předpisy po termínu splatnosti',
        'payments_month' => 'Příjmy (tento měsíc)',
        'payments_month_desc' => 'Celkem přijaté platby v tomto měsíci',
    ],
];
