<?php

namespace App\Filament\Widgets;

use App\Models\PlayerProfile;
use App\Models\User;
use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-banner-widget';

    // Priorita řazení widgetů na dashboardu (nižší = výš). Chceme úplně první.
    protected static ?int $sort = -200;

    // Na menších displejích přes celou šířku, od md vedle sebe (poloviční šířka)
    protected int|string|array $columnSpan = [
        'md' => 1,
    ];

    protected function getViewData(): array
    {
        $activePlayers = class_exists(PlayerProfile::class)
            ? PlayerProfile::count()
            : 0;

        $userName = auth()->user()?->name ?: 'Admin';

        // Quick actions URLs – use Filament Resource URLs if available, else fallback to admin path.
        $adminPath = config('filament.panels.admin.path', 'admin');

        $matchCreate = method_exists(\App\Filament\Resources\BasketballMatches\BasketballMatchResource::class, 'getUrl')
            ? \App\Filament\Resources\BasketballMatches\BasketballMatchResource::getUrl('create')
            : url("/{$adminPath}/basketball-matches/create");

        $userCreate = method_exists(\App\Filament\Resources\Users\UserResource::class, 'getUrl')
            ? \App\Filament\Resources\Users\UserResource::getUrl('create')
            : url("/{$adminPath}/users/create");

        $postCreate = class_exists(\App\Filament\Resources\Posts\PostResource::class) && method_exists(\App\Filament\Resources\Posts\PostResource::class, 'getUrl')
            ? \App\Filament\Resources\Posts\PostResource::getUrl('create')
            : url("/{$adminPath}/posts/create");

        $trainingCreate = class_exists(\App\Filament\Resources\Trainings\TrainingResource::class) && method_exists(\App\Filament\Resources\Trainings\TrainingResource::class, 'getUrl')
            ? \App\Filament\Resources\Trainings\TrainingResource::getUrl('create')
            : url("/{$adminPath}/trainings/create");

        $eventCreate = class_exists(\App\Filament\Resources\ClubEvents\ClubEventResource::class) && method_exists(\App\Filament\Resources\ClubEvents\ClubEventResource::class, 'getUrl')
            ? \App\Filament\Resources\ClubEvents\ClubEventResource::getUrl('create')
            : url("/{$adminPath}/club-events/create");

        $mediaUpload = class_exists(\App\Filament\Resources\MediaAssets\MediaAssetResource::class) && method_exists(\App\Filament\Resources\MediaAssets\MediaAssetResource::class, 'getUrl')
            ? \App\Filament\Resources\MediaAssets\MediaAssetResource::getUrl('index')
            : url("/{$adminPath}/media-assets");

        $auditLog = class_exists(\App\Filament\Resources\AuditLogs\AuditLogResource::class) && method_exists(\App\Filament\Resources\AuditLogs\AuditLogResource::class, 'getUrl')
            ? \App\Filament\Resources\AuditLogs\AuditLogResource::getUrl('index')
            : url("/{$adminPath}/audit-logs");

        $finance = class_exists(\App\Filament\Resources\FinanceCharges\FinanceChargeResource::class) && method_exists(\App\Filament\Resources\FinanceCharges\FinanceChargeResource::class, 'getUrl')
            ? \App\Filament\Resources\FinanceCharges\FinanceChargeResource::getUrl('index')
            : url("/{$adminPath}/finance-charges");

        return [
            'userName' => $userName,
            'activePlayers' => $activePlayers,
            'actions' => [
                'new_match' => $matchCreate,
                'new_user' => $userCreate,
                'new_post' => $postCreate,
                'new_training' => $trainingCreate,
                'new_event' => $eventCreate,
                'media_upload' => $mediaUpload,
                'audit_log' => $auditLog,
                'finance' => $finance,
            ],
        ];
    }
}
