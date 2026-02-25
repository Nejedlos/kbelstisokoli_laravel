<?php

return [
    'title' => 'Nastavení reCAPTCHA',
    'navigation' => 'reCAPTCHA',
    'sections' => [
        'general' => 'Obecné nastavení reCAPTCHA v3',
        'general_desc' => 'Zde nastavte klíče a parametry pro ochranu formulářů proti spamu.',
    ],
    'fields' => [
        'site_key' => 'Site Key',
        'secret_key' => 'Secret Key',
        'threshold' => 'Práh citlivosti (Threshold)',
        'threshold_help' => 'Hodnota mezi 0.0 (pravděpodobně bot) a 1.0 (pravděpodobně člověk). Doporučená hodnota je 0.5.',
        'enabled' => 'Aktivovat ochranu',
    ],
    'notifications' => [
        'saved' => 'Nastavení reCAPTCHA bylo úspěšně uloženo.',
        'error' => 'Při ukládání nastavení došlo k chybě.',
    ],
    'actions' => [
        'save' => 'Uložit nastavení',
    ],
];
