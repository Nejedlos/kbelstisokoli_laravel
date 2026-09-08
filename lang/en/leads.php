<?php

return [
    'types' => ['contact' => 'Contact message', 'recruitment' => 'Interest in playing'],
    'statuses' => ['new' => 'New', 'pending' => 'New', 'in_progress' => 'In progress', 'deferred' => 'Deferred', 'accepted' => 'Accepted', 'rejected' => 'Not accepted', 'processed' => 'Processed'],
    'fields' => ['type' => 'Type', 'status' => 'Status', 'responsible' => 'Responsible person', 'name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'subject' => 'Subject', 'message' => 'Message', 'team' => 'Team', 'birth_year' => 'Birth year', 'age' => 'Age', 'position' => 'Position', 'level' => 'Level', 'internal_notes' => 'Internal notes', 'next_contact_at' => 'Next contact', 'created_at' => 'Received'],
    'not_provided' => 'Not provided',
    'mail' => ['new_subject' => 'New lead: :name', 'assigned_subject' => 'Lead assigned: :name', 'new_title' => 'New prospect', 'assigned_title' => 'A lead was assigned to you', 'open_lead' => 'Open lead in admin'],
];
