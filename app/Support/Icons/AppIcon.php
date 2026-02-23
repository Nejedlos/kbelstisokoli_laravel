<?php

namespace App\Support\Icons;

/**
 * Centrální seznam klíčů pro ikony v aplikaci.
 * Slouží k zajištění konzistence a snadné hromadné změně.
 */
enum AppIcon: string
{
    // --- Moduly / Navigace ---
    case DASHBOARD = 'chart-line';
    case USERS = 'users';
    case ROLES = 'user-shield';
    case PERMISSIONS = 'key';
    case PLAYER_PROFILES = 'id-card';
    case PAGES = 'file-lines';
    case POSTS = 'newspaper';
    case CATEGORIES = 'tags';
    case MENUS = 'bars';
    case TRAININGS = 'dumbbell';
    case MATCHES = 'basketball';
    case EVENTS = 'calendar-check';
    case SEASONS = 'calendar-days';
    case TEAMS = 'user-group';
    case COMPETITIONS = 'trophy';
    case OPPONENTS = 'shield';
    case STAT_SOURCES = 'cloud-arrow-down';
    case STAT_SETS = 'chart-column';
    case FINANCE_CHARGES = 'file-invoice-dollar';
    case FINANCE_PAYMENTS = 'money-bill-transfer';
    case MEDIA_LIBRARY = 'images';
    case GALLERIES = 'film';
    case ANNOUNCEMENTS = 'bullhorn';
    case AUDIT_LOGS = 'clipboard-list';
    case REDIRECTS = 'shuffle';
    case CRON_TASKS = 'clock';
    case CRON_LOGS = 'history';
    case BRANDING = 'palette';
    case SETTINGS = 'gear';

    // --- UI Akce a Stavy ---
    case CREATE = 'circle-plus';
    case EDIT = 'pencil';
    case DELETE = 'trash-can';
    case VIEW = 'eye';
    case SAVE = 'floppy-disk';
    case CANCEL = 'xmark';
    case INVITE = 'paper-plane';
    case ACTIVATE = 'circle-check';
    case DEACTIVATE = 'circle-xmark';
    case REFRESH = 'arrows-rotate';
    case INFO = 'circle-info';
    case TABLE = 'table-cells';
    case FILTER = 'filter';
    case GLOBE = 'globe';
    case UPLOAD = 'file-arrow-up';
    case TRASH = 'trash';
    case ERASER = 'eraser';
    case COPY = 'copy';
    case CODE = 'code';
    case TERMINAL = 'terminal';
    case PHOTO_FILM = 'photo-film';
    case SEO = 'magnifying-glass';
    case LIST = 'list-ul';
    case CLUB = 'building-columns';
    case BADGE = 'id-badge';
    case ACCOUNT = 'address-card';
    case SECURITY = 'lock';
    case USER_GEAR = 'user-gear';
    case USER = 'user';
    case PHONE = 'phone';
    case LOCATION = 'location-dot';
    case EMERGENCY = 'truck-medical';
    case PHYSICAL = 'weight-scale';
    case NOTE = 'comment-medical';
    case AUDIT = 'clock-rotate-left';
    case BANKNOTES = 'money-bill';

    /**
     * Získá výchozí styl pro danou ikonu.
     */
    public function defaultStyle(): string
    {
        return 'fas';
    }
}
