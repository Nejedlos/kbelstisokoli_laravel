<?php

namespace App\Support;

use App\Support\Icons\AppIcon;
use Illuminate\Support\Str;

/**
 * Centrální správa ikon pro Filament administraci.
 * Zajišťuje konzistenci, fallbacky a snadnou změnu celé sady ikon.
 *
 * @deprecated Používejte App\Support\FilamentIcon
 */
class IconHelper
{
    // --- Doménové ikony (Klíče) ---
    public const USERS = AppIcon::USERS;
    public const ROLES = AppIcon::ROLES;
    public const PERMISSIONS = AppIcon::PERMISSIONS;
    public const PLAYER_PROFILES = AppIcon::PLAYER_PROFILES;
    public const PAGES = AppIcon::PAGES;
    public const POSTS = AppIcon::POSTS;
    public const CATEGORIES = AppIcon::CATEGORIES;
    public const MENUS = AppIcon::MENUS;
    public const TRAININGS = AppIcon::TRAININGS;
    public const MATCHES = AppIcon::MATCHES;
    public const EVENTS = AppIcon::EVENTS;
    public const SEASONS = AppIcon::SEASONS;
    public const TEAMS = AppIcon::TEAMS;
    public const COMPETITIONS = AppIcon::COMPETITIONS;
    public const OPPONENTS = AppIcon::OPPONENTS;
    public const STAT_SOURCES = AppIcon::STAT_SOURCES;
    public const STAT_SETS = AppIcon::STAT_SETS;
    public const FINANCE_CHARGES = AppIcon::FINANCE_CHARGES;
    public const FINANCE_PAYMENTS = AppIcon::FINANCE_PAYMENTS;
    public const MEDIA_LIBRARY = AppIcon::MEDIA_LIBRARY;
    public const GALLERIES = AppIcon::GALLERIES;
    public const ANNOUNCEMENTS = AppIcon::ANNOUNCEMENTS;
    public const AUDIT_LOGS = AppIcon::AUDIT_LOGS;
    public const REDIRECTS = AppIcon::REDIRECTS;
    public const CRON_TASKS = AppIcon::CRON_TASKS;
    public const CRON_LOGS = AppIcon::CRON_LOGS;
    public const BRANDING = AppIcon::BRANDING;
    public const DASHBOARD = AppIcon::DASHBOARD;
    public const SETTINGS = AppIcon::SETTINGS;

    // --- UI Akce ---
    public const CREATE = AppIcon::CREATE;
    public const EDIT = AppIcon::EDIT;
    public const DELETE = AppIcon::DELETE;
    public const VIEW = AppIcon::VIEW;
    public const SAVE = AppIcon::SAVE;
    public const CANCEL = AppIcon::CANCEL;
    public const INVITE = AppIcon::INVITE;
    public const ACTIVATE = AppIcon::ACTIVATE;
    public const DEACTIVATE = AppIcon::DEACTIVATE;
    public const REFRESH = AppIcon::REFRESH;
    public const INFO = AppIcon::INFO;
    public const TABLE = AppIcon::TABLE;
    public const FILTER = AppIcon::FILTER;
    public const GLOBE = AppIcon::GLOBE;
    public const UPLOAD = AppIcon::UPLOAD;
    public const METADATA = AppIcon::INFO;
    public const PLUS = AppIcon::CREATE;
    public const TRASH = AppIcon::TRASH;
    public const EYE = AppIcon::VIEW;
    public const PLAY = AppIcon::CRON_TASKS;
    public const IMAGE = AppIcon::MEDIA_LIBRARY;
    public const COPY = AppIcon::COPY;
    public const CODE = AppIcon::CODE;
    public const PAPER_PLANE = AppIcon::INVITE;
    public const PEN_NIB = AppIcon::EDIT;
    public const PHOTO_FILM = AppIcon::PHOTO_FILM;
    public const SEO = AppIcon::SEO;
    public const TERMINAL = AppIcon::TERMINAL;
    public const ARROWS_ROTATE = AppIcon::REFRESH;
    public const CIRCLE_CHECK = AppIcon::ACTIVATE;
    public const CIRCLE_XMARK = AppIcon::DEACTIVATE;
    public const SHUFFLE = AppIcon::REDIRECTS;
    public const HISTORY = AppIcon::CRON_LOGS;
    public const CLOCK = AppIcon::CRON_TASKS;
    public const PALETTE = AppIcon::BRANDING;
    public const LIST = AppIcon::LIST;
    public const SEND = AppIcon::INVITE;
    public const CLUB = AppIcon::CLUB;
    public const BADGE = AppIcon::BADGE;
    public const BASKETBALL = AppIcon::MATCHES;
    public const ACCOUNT = AppIcon::ACCOUNT;
    public const SECURITY = AppIcon::SECURITY;
    public const USER_GEAR = AppIcon::USER_GEAR;
    public const USER = AppIcon::USER;
    public const USERS_GROUP = AppIcon::TEAMS;
    public const SHIELD = AppIcon::OPPONENTS;
    public const SHIELD_CHECK = AppIcon::SECURITY;
    public const GEARS = AppIcon::SETTINGS;
    public const PHONE = AppIcon::PHONE;
    public const IDENTITY = AppIcon::PLAYER_PROFILES;
    public const BANKNOTES = AppIcon::BANKNOTES;
    public const MONEY = AppIcon::BANKNOTES;
    public const CHECK_CIRCLE = AppIcon::ACTIVATE;
    public const RECIPIENT = AppIcon::USERS;
    public const LOCATION = AppIcon::LOCATION;
    public const EMERGENCY = AppIcon::EMERGENCY;
    public const PHYSICAL = AppIcon::PHYSICAL;
    public const NOTE = AppIcon::NOTE;
    public const AUDIT = AppIcon::AUDIT;

    /**
     * Získá název ikony nebo aliasu.
     *
     * @param string|AppIcon $icon Klíč ikony
     * @param string $style Styl ikony
     * @return string
     */
    public static function get(string|AppIcon $icon, string $style = 'fal'): string
    {
        return FilamentIcon::get($icon, $style);
    }

    /**
     * Vyrenderuje ikonu přímo jako HtmlString (pro použití uvnitř vlastních HTML řetězců).
     *
     * @param string $icon Klíč ikony nebo název ikony
     * @param string $style Styl ikony
     * @return \Illuminate\Support\HtmlString
     */
    public static function render(string|AppIcon $icon, string $style = 'fal'): \Illuminate\Support\HtmlString
    {
        return FilamentIcon::render($icon, $style);
    }

    /**
     * Bezpečné získání ikony s garantovaným fallbackem na otazník.
     */
    public static function safe(string|AppIcon $icon, string $fallback = 'heroicon-o-question-mark-circle'): string
    {
        return FilamentIcon::safe($icon, $fallback);
    }

    /**
     * Alias pro get() se stylem Solid (fas).
     */
    public static function solid(string|AppIcon $icon): string
    {
        return FilamentIcon::solid($icon);
    }

    /**
     * Alias pro get() se stylem Regular (far).
     */
    public static function regular(string|AppIcon $icon): string
    {
        return FilamentIcon::regular($icon);
    }

    /**
     * Alias pro get() se stylem Light (fal).
     */
    public static function light(string|AppIcon $icon): string
    {
        return FilamentIcon::light($icon);
    }
}
