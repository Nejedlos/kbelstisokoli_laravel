<?php

return [
    'reminder' => [
        'subject' => ':team | Your attendance is still missing',
        'badge' => 'Team attendance',
        'heading' => 'Will you be there?',
        'preheader' => 'The :team event is waiting for your response.',
        'intro' => 'Hi :name, you have not responded to the following team event yet.',
        'motivation' => 'A quick response helps the coaches prepare and your teammates plan the line-up. It only takes one click.',
        'secure_note' => 'You will briefly confirm your choice after clicking. This protects attendance from automated email scanners.',
        'match_against' => 'Game against :opponent',
        'event_fallback' => 'Team event',
    ],
    'summary' => ['subject' => ':team | Today’s attendance summary', 'badge' => 'Today’s line-up', 'heading' => 'Today’s team line-up', 'preheader' => 'Confirmed and missing attendance for :team.', 'roster_label' => 'Team overview · :count members'],
    'actions' => ['yes' => 'I’m coming', 'no' => 'I can’t come', 'detail' => 'View event details', 'open_roster' => 'Open full attendance', 'confirm' => 'Confirm choice'],
    'status' => ['confirmed' => 'Coming', 'declined' => 'Not coming', 'maybe' => 'Maybe', 'pending' => 'No response'],
    'unsubscribe' => ['text' => 'You can disable these reminders in settings or', 'summary_text' => 'You can disable these summaries in settings or', 'link' => 'unsubscribe here'],
    'response' => ['confirm' => 'Please confirm your choice', 'confirm_unsubscribe' => 'Do you really want to disable these email notifications?', 'saved' => 'Your attendance has been saved.', 'too_late' => 'Attendance can no longer be changed using this link.', 'not_allowed' => 'This event is not assigned to your team.', 'unsubscribed' => 'Email notifications have been disabled. You can enable them again in your profile.'],
    'settings' => ['title' => 'Attendance email notifications', 'reminders' => 'My attendance reminders', 'reminders_help' => 'Reminders 7 days, 3 days and shortly before an event when you have not responded.', 'summaries' => 'Team summaries on event day', 'summaries_help' => 'Coming / not coming / no response overview for team members and coaches.'],
];
