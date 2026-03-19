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
        'users' => Icons::USERS,
        'roles' => Icons::ROLES,
        'permissions' => Icons::PERMISSIONS,
        'player_profiles' => Icons::PLAYER_PROFILES,
        'pages' => Icons::PAGES,
        'posts' => Icons::POSTS,
        'categories' => Icons::CATEGORIES,
        'menus' => Icons::MENUS,
        'trainings' => Icons::TRAININGS,
        'matches' => Icons::MATCHES,
        'events' => Icons::EVENTS,
        'seasons' => Icons::SEASONS,
        'teams' => Icons::TEAMS,
        'competitions' => Icons::COMPETITIONS,
        'opponents' => Icons::OPPONENTS,
        'stat_sources' => Icons::STAT_SOURCES,
        'stat_sets' => Icons::STAT_SETS,
        'finance_charges' => Icons::FINANCE_CHARGES,
        'finance_payments' => Icons::FINANCE_PAYMENTS,
        'media_library' => Icons::MEDIA_LIBRARY,
        'galleries' => Icons::GALLERIES,
        'announcements' => Icons::ANNOUNCEMENTS,
        'audit_logs' => Icons::AUDIT_LOGS,
        'redirects' => Icons::REDIRECTS,
        'cron_tasks' => Icons::CRON_TASKS,
        'cron_logs' => Icons::CRON_LOGS,
        'branding' => Icons::BRANDING,
        'dashboard' => Icons::DASHBOARD,
        'settings' => Icons::SETTINGS,
    ],

    'actions' => [
        'create' => Icons::CREATE,
        'edit' => Icons::EDIT,
        'delete' => Icons::DELETE,
        'view' => Icons::VIEW,
        'save' => Icons::SAVE,
        'cancel' => Icons::CANCEL,
        'invite' => Icons::INVITE,
        'activate' => Icons::ACTIVATE,
        'deactivate' => Icons::DEACTIVATE,
        'refresh' => Icons::REFRESH,
        'info' => Icons::INFO,
        'table' => Icons::TABLE,
        'filter' => Icons::FILTER,
        'globe' => Icons::GLOBE,
    ],

];
