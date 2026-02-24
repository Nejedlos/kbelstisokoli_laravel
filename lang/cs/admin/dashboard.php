<?php

return [
    'welcome' => [
        'title' => 'Vítejte zpět, :name!',
        'text' => 'V klubu je aktuálně :active_players aktivních hráčů a systém běží bez chyb.',
        'quick_actions' => [
            'new_match' => 'Nový zápas',
            'new_user' => 'Nový člen',
            'new_post' => 'Napsat novinku',
        ],
    ],

    'club_health' => [
        'title' => 'Stav oddílu',
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
