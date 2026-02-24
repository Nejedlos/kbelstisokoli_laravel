<?php

return [
    'welcome' => [
        'title' => 'Welcome back, :name!',
        'text' => 'There are currently :active_players active players and the system is running smoothly.',
        'quick_actions' => [
            'new_match' => 'New Match',
            'new_user' => 'New Member',
            'new_post' => 'Write News',
        ],
    ],

    'club_health' => [
        'title' => 'Club Health',
    ],

    'system' => [
        'title' => 'System Status',
        'cron' => [
            'label' => 'Scheduler (Cron)',
            'ok' => 'OK',
            'problem' => 'Issue',
            'last_run' => 'Last run: :time',
            'unknown' => 'Unknown',
        ],
        'ai' => [
            'label' => 'AI Index',
            'ready' => 'Ready',
            'needs_index' => 'Needs indexing',
        ],
    ],

    'recent_activity' => [
        'title' => 'Recent Activity',
        'empty' => 'No records to display.',
        'actor_system' => 'System',
    ],

    'kpi' => [
        'users_total' => 'Users (total)',
        'users_active_desc' => 'Active: :count',
        'players_total' => 'Player Profiles',
        'teams_total' => 'Teams',
        'matches_total' => 'Matches',
        'matches_upcoming_desc' => 'Upcoming: :count',
        'trainings_total' => 'Trainings',
        'trainings_upcoming_desc' => 'Upcoming: :count',
        'attendance_total' => 'RSVP/Attendance',
        'attendance_desc' => 'Total records',
    ],

    'finance' => [
        'total_receivables' => 'Total Receivables',
        'total_receivables_desc' => 'Open and partially paid charges',
        'overdue' => 'Overdue',
        'overdue_desc' => 'Charges past due date',
        'payments_month' => 'Income (this month)',
        'payments_month_desc' => 'Total payments received this month',
    ],
];
