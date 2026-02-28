<?php

use App\Support\IconHelper;
use App\Support\Icons\AppIcon as Icons;

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
        'users' => IconHelper::get(Icons::USERS),
        'roles' => IconHelper::get(Icons::ROLES),
        'permissions' => IconHelper::get(Icons::PERMISSIONS),
        'player_profiles' => IconHelper::get(Icons::PLAYER_PROFILES),
        'pages' => IconHelper::get(Icons::PAGES),
        'posts' => IconHelper::get(Icons::POSTS),
        'categories' => IconHelper::get(Icons::CATEGORIES),
        'menus' => IconHelper::get(Icons::MENUS),
        'trainings' => IconHelper::get(Icons::TRAININGS),
        'matches' => IconHelper::get(Icons::MATCHES),
        'events' => IconHelper::get(Icons::EVENTS),
        'seasons' => IconHelper::get(Icons::SEASONS),
        'teams' => IconHelper::get(Icons::TEAMS),
        'competitions' => IconHelper::get(Icons::COMPETITIONS),
        'opponents' => IconHelper::get(Icons::OPPONENTS),
        'stat_sources' => IconHelper::get(Icons::STAT_SOURCES),
        'stat_sets' => IconHelper::get(Icons::STAT_SETS),
        'finance_charges' => IconHelper::get(Icons::FINANCE_CHARGES),
        'finance_payments' => IconHelper::get(Icons::FINANCE_PAYMENTS),
        'media_library' => IconHelper::get(Icons::MEDIA_LIBRARY),
        'galleries' => IconHelper::get(Icons::GALLERIES),
        'announcements' => IconHelper::get(Icons::ANNOUNCEMENTS),
        'audit_logs' => IconHelper::get(Icons::AUDIT_LOGS),
        'redirects' => IconHelper::get(Icons::REDIRECTS),
        'cron_tasks' => IconHelper::get(Icons::CRON_TASKS),
        'cron_logs' => IconHelper::get(Icons::CRON_LOGS),
        'branding' => IconHelper::get(Icons::BRANDING),
        'dashboard' => IconHelper::get(Icons::DASHBOARD),
        'settings' => IconHelper::get(Icons::SETTINGS),
    ],

    'actions' => [
        'create' => IconHelper::get(Icons::CREATE),
        'edit' => IconHelper::get(Icons::EDIT),
        'delete' => IconHelper::get(Icons::DELETE),
        'view' => IconHelper::get(Icons::VIEW),
        'save' => IconHelper::get(Icons::SAVE),
        'cancel' => IconHelper::get(Icons::CANCEL),
        'invite' => IconHelper::get(Icons::INVITE),
        'activate' => IconHelper::get(Icons::ACTIVATE),
        'deactivate' => IconHelper::get(Icons::DEACTIVATE),
        'refresh' => IconHelper::get(Icons::REFRESH),
        'info' => IconHelper::get(Icons::INFO),
        'table' => IconHelper::get(Icons::TABLE),
        'filter' => IconHelper::get(Icons::FILTER),
        'globe' => IconHelper::get(Icons::GLOBE),
    ],

];
