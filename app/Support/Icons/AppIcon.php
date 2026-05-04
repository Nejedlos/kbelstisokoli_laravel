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
    case NOT_FOUND = 'ghost';
    case REDIRECTS = 'shuffle';
    case CRON_TASKS = 'clock';
    case CRON_LOGS = 'history';
    case BRANDING = 'palette';
    case AI = 'sparkles';
    case RECAPTCHA = 'shield-halved';
    case SETTINGS = 'gear';
    case GAUGE = 'gauge-high';
    case BUG = 'bug';
    case BOLT = 'bolt';
    case MICROCHIP = 'microchip';
    case BROOM_WIDE = 'broom-wide';
    case DATABASE = 'database';
    case ROCKET = 'rocket';
    case HAMMER = 'hammer';
    case STETHOSCOPE = 'stethoscope';
    case GEARS = 'gears';
    case ARCHIVE = 'box-archive';
    case BELL = 'bell';
    case UNDO = 'undo';
    case SEEDLING = 'seedling';
    case ROUTE = 'route';
    case LINK = 'link';
    case WRENCH = 'wrench';
    case PACKAGE = 'box-open';
    case BRANCH = 'code-branch';
    case SLIDERS = 'sliders';
    case PHP = 'php';
    case NODE_JS = 'node-js';
    case LOGIN = 'right-to-bracket';
    case LOGOUT = 'right-from-bracket';
    case WARNING = 'circle-exclamation';
    case TRASH_CAN_ARROW_UP = 'trash-can-arrow-up';
    case DMARC = 'envelope-shield';
    case MAILBOX = 'envelopes-bulk';

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
    case DOWNLOAD = 'download';
    case COPY = 'copy';
    case PLAY = 'play';
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
    case USER_SECRET = 'user-secret';
    case PARTNERS = 'handshake';
    case DOCUMENTATION = 'book';
    case SHOE_PRINTS = 'shoe-prints';
    case NETWORK = 'network-wired';
    case HELP = 'circle-question';
    case ATTENDANCE = 'user-check';
    case HEARTBEAT = 'heartbeat';

    /**
     * Získá výchozí styl pro danou ikonu.
     */
    public function defaultStyle(): string
    {
        return 'fas';
    }
}
