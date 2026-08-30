<?php

return [
    'reminder' => [
        'subject' => ':team | Čekáme na tvoji docházku',
        'badge' => 'Docházka týmu',
        'heading' => 'Dáš nám vědět, jestli dorazíš?',
        'preheader' => 'Akce týmu :team čeká na tvoji odpověď.',
        'intro' => 'Ahoj :name, u následující týmové akce ještě nemáš zadanou docházku.',
        'motivation' => 'Rychlou odpovědí pomůžeš trenérům s přípravou a spoluhráčům s plánováním sestavy. Zabere to jen jedno kliknutí.',
        'secure_note' => 'Po kliknutí volbu pouze krátce potvrdíte. Chráníme tím docházku před automatickými e-mailovými roboty.',
        'match_against' => 'Zápas proti :opponent',
        'event_fallback' => 'Týmová akce',
    ],
    'summary' => [
        'subject' => ':team | Dnešní přehled docházky',
        'badge' => 'Dnešní sestava',
        'heading' => 'Dnešní týmová sestava',
        'preheader' => 'Přehled potvrzené a chybějící docházky týmu :team.',
        'roster_label' => 'Přehled týmu · :count členů',
    ],
    'actions' => ['yes' => 'Přijdu', 'no' => 'Nepřijdu', 'detail' => 'Zobrazit detail akce', 'open_roster' => 'Otevřít úplný přehled', 'confirm' => 'Potvrdit volbu'],
    'status' => ['confirmed' => 'Přijdou', 'declined' => 'Nepřijdou', 'maybe' => 'Možná', 'pending' => 'Bez odpovědi'],
    'unsubscribe' => ['text' => 'Tyto připomínky můžete vypnout v nastavení nebo', 'summary_text' => 'Tyto souhrny můžete vypnout v nastavení nebo', 'link' => 'jedním kliknutím zde'],
    'response' => ['confirm' => 'Potvrďte prosím svou volbu', 'confirm_unsubscribe' => 'Opravdu chcete vypnout tato e-mailová upozornění?', 'saved' => 'Docházka byla uložena.', 'too_late' => 'Docházku už nelze tímto odkazem změnit.', 'not_allowed' => 'Tato akce není určena vašemu týmu.', 'unsubscribed' => 'E-mailová upozornění byla vypnuta. Kdykoliv je můžete znovu zapnout v profilu.'],
    'settings' => ['title' => 'E-mailová upozornění na docházku', 'reminders' => 'Připomínky mé docházky', 'reminders_help' => 'Upozornění 7 dní, 3 dny a krátce před akcí, pokud jste ještě neodpověděli.', 'summaries' => 'Týmové souhrny v den akce', 'summaries_help' => 'Přehled přijdu / nepřijdu / bez odpovědi pro členy a trenéry vašich týmů.'],
];
