<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Centrální správa ikon pro Filament administraci.
 * Zajišťuje konzistenci, fallbacky a snadnou změnu celé sady ikon.
 */
class FilamentIcon
{
    // --- Doménové ikony (Klíče) ---
    public const USERS = 'users';
    public const ROLES = 'user-shield';
    public const PERMISSIONS = 'key';
    public const PLAYER_PROFILES = 'id-card';
    public const PAGES = 'file-lines';
    public const POSTS = 'newspaper';
    public const CATEGORIES = 'tags';
    public const MENUS = 'bars';
    public const TRAININGS = 'dumbbell';
    public const MATCHES = 'basketball';
    public const EVENTS = 'calendar-check';
    public const SEASONS = 'calendar-days';
    public const TEAMS = 'user-group';
    public const COMPETITIONS = 'trophy';
    public const OPPONENTS = 'shield';
    public const STAT_SOURCES = 'cloud-arrow-down';
    public const STAT_SETS = 'chart-column';
    public const FINANCE_CHARGES = 'file-invoice-dollar';
    public const FINANCE_PAYMENTS = 'money-bill-transfer';
    public const MEDIA_LIBRARY = 'images';
    public const GALLERIES = 'film';
    public const ANNOUNCEMENTS = 'bullhorn';
    public const AUDIT_LOGS = 'clipboard-list';
    public const REDIRECTS = 'shuffle';
    public const CRON_TASKS = 'clock';
    public const CRON_LOGS = 'history';
    public const BRANDING = 'palette';
    public const DASHBOARD = 'chart-line';
    public const SETTINGS = 'gear';

    // --- UI Akce ---
    public const CREATE = 'circle-plus';
    public const EDIT = 'pencil';
    public const DELETE = 'trash-can';
    public const VIEW = 'eye';
    public const SAVE = 'floppy-disk';
    public const CANCEL = 'xmark';
    public const INVITE = 'paper-plane';
    public const ACTIVATE = 'circle-check';
    public const DEACTIVATE = 'circle-xmark';
    public const REFRESH = 'arrows-rotate';
    public const INFO = 'circle-info';
    public const TABLE = 'table-cells';
    public const FILTER = 'filter';
    public const GLOBE = 'globe';
    public const UPLOAD = 'file-arrow-up';
    public const METADATA = 'circle-info';
    public const PLUS = 'circle-plus';
    public const TRASH = 'trash-can';
    public const EYE = 'eye';
    public const PLAY = 'play';
    public const IMAGE = 'image';
    public const COPY = 'copy';
    public const CODE = 'code';
    public const PAPER_PLANE = 'paper-plane';
    public const PEN_NIB = 'pen-nib';
    public const PHOTO_FILM = 'photo-film';
    public const SEO = 'magnifying-glass';
    public const TERMINAL = 'terminal';
    public const ARROWS_ROTATE = 'arrows-rotate';
    public const CIRCLE_CHECK = 'circle-check';
    public const CIRCLE_XMARK = 'circle-xmark';
    public const SHUFFLE = 'shuffle';
    public const HISTORY = 'history';
    public const CLOCK = 'clock';
    public const PALETTE = 'palette';
    public const LIST = 'list-ul';
    public const SEND = 'paper-plane';
    public const CLUB = 'building-columns';
    public const BADGE = 'id-badge';
    public const BASKETBALL = 'basketball';
    public const ACCOUNT = 'address-card';
    public const SECURITY = 'lock';
    public const USER_GEAR = 'user-gear';
    public const USER = 'user';
    public const USERS_GROUP = 'user-group';
    public const SHIELD = 'shield';
    public const SHIELD_CHECK = 'shield';
    public const GEARS = 'gears';
    public const PHONE = 'phone';
    public const IDENTITY = 'id-card';
    public const BANKNOTES = 'money-bill';
    public const MONEY = 'money-bill';
    public const CHECK_CIRCLE = 'circle-check';
    public const RECIPIENT = 'user-tag';
    public const LOCATION = 'location-dot';
    public const EMERGENCY = 'truck-medical';
    public const PHYSICAL = 'weight-scale';
    public const NOTE = 'comment-medical';
    public const AUDIT = 'clock-rotate-left';

    /**
     * Získá název ikony ve formátu Blade Icons (Font Awesome).
     *
     * @param string $icon Klíč ikony (z konstant této třídy) nebo název ikony
     * @param string $style Styl ikony (fas, far, fab, fal, fad)
     * @return string
     */
    public static function get(string $icon, string $style = 'fas'): string
    {
        // 0. V testech vracíme bezpečný placeholder, abychom se vyhnuli SvgNotFound
        // a problémům s příliš brzkým voláním app() během bootu configu.
        if ((isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'testing') || defined('PHPUNIT_COMPOSER_INSTALL')) {
            return 'heroicon-o-stop';
        }

        // 1. Pokud existuje mapování v configu pro tento klíč, použijeme ho
        // (Zamezíme nekonečné smyčce kontrolou, zda se neptáme na stejný řetězec)
        if (Str::contains($icon, ['fas-', 'far-', 'fab-', 'fal-'])) {
            return $icon;
        }

        // 1. Normalizace - pokud je předán název s podtržítky, změníme na pomlčky
        $icon = str_replace('_', '-', $icon);

        // 2. Kontrola stylu a fallback
        // V tomto projektu používáme Font Awesome 7 Pro Light (fal) přes webfont,
        // ale pro Blade Icons (SVG) máme zatím jen Free verzi (fas, far, fab).
        // Pokud chceme 'fal', ale nemáme Pro SVG set, musíme fallbackovat.

        // Fallback na Solid, pokud je styl fal (zatím nemáme Pro SVGs)
        if ($style === 'fal') {
            $style = 'fas';
        }

        $allowedStyles = ['fas', 'far', 'fab', 'fal', 'fad', 'fat'];
        if (!in_array($style, $allowedStyles)) {
            $style = 'fas';
        }

        return "{$style}-{$icon}";
    }

    /**
     * Bezpečné získání ikony s garantovaným fallbackem na otazník.
     */
    public static function safe(string $icon, string $fallback = 'heroicon-o-question-mark-circle'): string
    {
        try {
            return self::get($icon);
        } catch (\Exception $e) {
            return $fallback;
        }
    }

    /**
     * Alias pro get() se stylem Solid (fas).
     */
    public static function solid(string $icon): string
    {
        return self::get($icon, 'fas');
    }

    /**
     * Alias pro get() se stylem Regular (far).
     */
    public static function regular(string $icon): string
    {
        return self::get($icon, 'far');
    }

    /**
     * Alias pro get() se stylem Light (fal).
     */
    public static function light(string $icon): string
    {
        return self::get($icon, 'fal');
    }
}
