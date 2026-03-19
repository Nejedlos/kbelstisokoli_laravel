<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Help System Version
    |--------------------------------------------------------------------------
    |
    | Tato hodnota určuje, která verze help systému se má použít.
    |
    | Možnosti:
    | 'v1' - Původní markdown-based systém (z docs/help)
    | 'v2' - Nový databázový systém (se seedy, rolemi, UX)
    |
    */
    'version' => env('HELP_SYSTEM_VERSION', 'v2'),
];
