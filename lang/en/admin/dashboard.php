<?php

return [
    'welcome' => [
        'title' => 'Welcome back, :name!',
        'text' => 'There are currently :active_players active players and the system is running smoothly.',
        'quick_actions' => [
            'new_match' => 'New Match',
            'new_match_hint' => 'Create a new match in the calendar',
            'new_user' => 'New Member',
            'new_user_hint' => 'Register a new member or coach',
            'new_post' => 'Write News',
            'new_post_hint' => 'Publish an article on web and app',
            'new_training' => 'New Training',
            'new_training_hint' => 'Plan a new training session',
            'new_event' => 'New Event',
            'new_event_hint' => 'Create a club event or camp',
            'media_upload' => 'Multimedia',
            'media_upload_hint' => 'Manage photos and videos in gallery',
            'audit_log' => 'Audit Log',
            'audit_log_hint' => 'View system change history',
            'finance' => 'Finance',
            'finance_cta' => 'Go to Finance',
            'finance_hint' => 'Overview of payments and member fees',
        ],
    ],

    'finance_summary' => [
        'cta' => 'Go to Finance',
    ],

    'club_health' => [
        'title' => 'Club Health',
    ],

    'contact_admin' => [
        'title' => 'Need help from the administrator?',
        'text' => 'For technical issues or system configuration, please contact the administrator Michal Nejedlý directly.',
        'cta' => 'Send Message',
        'mailto' => 'Send Email',
        'success_title' => 'Message has been sent to the administrator.',
        'fields' => [
            'subject' => 'Subject',
            'message' => 'Your message',
            'placeholder_subject' => 'What do you need help with?',
            'placeholder_message' => 'Describe your request in detail...',
        ],
        'subjects' => [
            'technical' => 'Technical issue',
            'access' => 'Access problem',
            'finance' => 'Finance query',
            'other' => 'Other',
        ],
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
        'actions' => [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'login' => 'Login',
            'password_reset' => 'Password Reset',
        ],
    ],

    'kpi' => [
        'users_total' => 'Users (total)',
        'users_active_desc' => 'Active: :count',
        'players_total' => 'Player Profiles',
        'teams_total' => 'Teams',
        'matches_total' => 'Matches',
        'matches_upcoming' => 'Upcoming matches',
        'matches_upcoming_desc' => 'Upcoming: :count',
        'trainings_total' => 'Trainings',
        'trainings_upcoming' => 'Upcoming trainings',
        'trainings_upcoming_desc' => 'Upcoming: :count',
        'attendance_total' => 'RSVP/Attendance',
        'attendance_desc' => 'Total records',
        'leads_total' => 'Leads / Contacts',
        'leads_pending_desc' => 'Pending: :count',
        'posts_total' => 'News',
        'posts_active_desc' => 'Published: :count',
    ],

    'health' => [
        'title' => 'System Status & Health',
        'mismatches' => 'Mismatches',
        'missing_configs' => 'No payment info',
        'renewal_warning' => 'Attention! Missing payer configurations for the new season.',
        'renewal_cta' => 'Set Payment Profiles :season',
    ],

    'agenda' => [
        'title' => 'Club Agenda',
        'empty' => 'No scheduled program.',
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
