<?php

use App\Support\FilamentIcon as Icons;

return [

    /*
    |--------------------------------------------------------------------------
    | Mapování doménových ikon aplikace
    |--------------------------------------------------------------------------
    |
    | Zde jsou definovány ikony pro jednotlivé moduly a akce.
    | Díky tomuto mapování lze snadno změnit ikony v celé aplikaci najednou.
    |
    | Formát: 'fas-users', 'heroicon-o-users', atd.
    |
    */

    'navigation' => [
        'users' => Icons::get(Icons::USERS),
        'roles' => Icons::get(Icons::ROLES),
        'permissions' => Icons::get(Icons::PERMISSIONS),
        'player_profiles' => Icons::get(Icons::PLAYER_PROFILES),
        'pages' => Icons::get(Icons::PAGES),
        'posts' => Icons::get(Icons::POSTS),
        'categories' => Icons::get(Icons::CATEGORIES),
        'menus' => Icons::get(Icons::MENUS),
        'trainings' => Icons::get(Icons::TRAININGS),
        'matches' => Icons::get(Icons::MATCHES),
        'events' => Icons::get(Icons::EVENTS),
        'seasons' => Icons::get(Icons::SEASONS),
        'teams' => Icons::get(Icons::TEAMS),
        'competitions' => Icons::get(Icons::COMPETITIONS),
        'opponents' => Icons::get(Icons::OPPONENTS),
        'stat_sources' => Icons::get(Icons::STAT_SOURCES),
        'stat_sets' => Icons::get(Icons::STAT_SETS),
        'finance_charges' => Icons::get(Icons::FINANCE_CHARGES),
        'finance_payments' => Icons::get(Icons::FINANCE_PAYMENTS),
        'media_library' => Icons::get(Icons::MEDIA_LIBRARY),
        'galleries' => Icons::get(Icons::GALLERIES),
        'announcements' => Icons::get(Icons::ANNOUNCEMENTS),
        'audit_logs' => Icons::get(Icons::AUDIT_LOGS),
        'redirects' => Icons::get(Icons::REDIRECTS),
        'cron_tasks' => Icons::get(Icons::CRON_TASKS),
        'cron_logs' => Icons::get(Icons::CRON_LOGS),
        'branding' => Icons::get(Icons::BRANDING),
        'dashboard' => Icons::get(Icons::DASHBOARD),
        'settings' => Icons::get(Icons::SETTINGS),
    ],

    'actions' => [
        'create' => Icons::get(Icons::CREATE),
        'edit' => Icons::get(Icons::EDIT),
        'delete' => Icons::get(Icons::DELETE),
        'view' => Icons::get(Icons::VIEW),
        'save' => Icons::get(Icons::SAVE),
        'cancel' => Icons::get(Icons::CANCEL),
        'invite' => Icons::get(Icons::INVITE),
        'activate' => Icons::get(Icons::ACTIVATE),
        'deactivate' => Icons::get(Icons::DEACTIVATE),
        'refresh' => Icons::get(Icons::REFRESH),
        'info' => Icons::get(Icons::INFO),
        'table' => Icons::get(Icons::TABLE),
        'filter' => Icons::get(Icons::FILTER),
        'globe' => Icons::get(Icons::GLOBE),
    ],

];
