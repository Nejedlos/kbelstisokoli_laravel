<?php

return [
    'types' => ['contact' => 'Kontaktní zpráva', 'recruitment' => 'Zájem o hraní'],
    'statuses' => ['new' => 'Nový', 'pending' => 'Nový', 'in_progress' => 'V řešení', 'deferred' => 'Odloženo', 'accepted' => 'Přijat', 'rejected' => 'Nepřijat', 'processed' => 'Zpracován'],
    'fields' => ['type' => 'Typ', 'status' => 'Stav', 'responsible' => 'Odpovědná osoba', 'name' => 'Jméno', 'email' => 'E-mail', 'phone' => 'Telefon', 'subject' => 'Předmět', 'message' => 'Zpráva', 'team' => 'Tým', 'birth_year' => 'Rok narození', 'age' => 'Věk', 'position' => 'Post', 'level' => 'Úroveň', 'internal_notes' => 'Interní poznámky', 'next_contact_at' => 'Další kontakt', 'created_at' => 'Přijato'],
    'not_provided' => 'Neuvedeno',
    'mail' => ['new_subject' => 'Nový lead: :name', 'assigned_subject' => 'Přiřazen lead: :name', 'new_title' => 'Nový zájemce', 'assigned_title' => 'Byl vám přiřazen lead', 'open_lead' => 'Otevřít lead v administraci'],
];
