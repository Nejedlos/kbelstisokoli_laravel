<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Filament\Resources\ClubEvents\ClubEventResource;
use App\Filament\Resources\FinanceCharges\FinanceChargeResource;
use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Trainings\TrainingResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\PlayerProfile;
use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-banner-widget';

    // Priorita řazení widgetů na dashboardu (nižší = výš). Chceme úplně první.
    protected static ?int $sort = -200;

    // Na menších displejích přes celou šířku, od md vedle sebe (poloviční šířka)
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    // Výška placeholderu při lazy načítání – stabilizuje layout a brání přeskakování.
    protected ?string $placeholderHeight = '11rem';

    /**
     * Placeholder načteme přes celou šířku (full), aby se dva pulzující bloky
     * vedle sebe nepřekrývaly uprostřed při načítání.
     */
    public function getPlaceholderData(): array
    {
        return [
            'columnSpan' => 'full',
            'columnStart' => [],
        ];
    }

    protected function getViewData(): array
    {
        $userName = auth()->user()?->name ?: 'Admin';
        $activePlayers = class_exists(PlayerProfile::class)
            ? PlayerProfile::count()
            : 0;

        // Quick actions URLs – use Filament Resource URLs if available, else fallback to admin path.
        $adminPath = config('filament.panels.admin.path', 'admin');

        $matchCreate = method_exists(BasketballMatchResource::class, 'getUrl')
            ? BasketballMatchResource::getUrl('create')
            : url("/{$adminPath}/basketball-matches/create");

        $userCreate = method_exists(UserResource::class, 'getUrl')
            ? UserResource::getUrl('create')
            : url("/{$adminPath}/users/create");

        $postCreate = class_exists(PostResource::class) && method_exists(PostResource::class, 'getUrl')
            ? PostResource::getUrl('create')
            : url("/{$adminPath}/posts/create");

        $trainingCreate = class_exists(TrainingResource::class) && method_exists(TrainingResource::class, 'getUrl')
            ? TrainingResource::getUrl('create')
            : url("/{$adminPath}/trainings/create");

        $eventCreate = class_exists(ClubEventResource::class) && method_exists(ClubEventResource::class, 'getUrl')
            ? ClubEventResource::getUrl('create')
            : url("/{$adminPath}/club-events/create");

        $mediaUpload = class_exists(MediaAssetResource::class) && method_exists(MediaAssetResource::class, 'getUrl')
            ? MediaAssetResource::getUrl('index')
            : url("/{$adminPath}/media-assets");

        $auditLog = class_exists(AuditLogResource::class) && method_exists(AuditLogResource::class, 'getUrl')
            ? AuditLogResource::getUrl('index')
            : url("/{$adminPath}/audit-logs");

        $finance = class_exists(FinanceChargeResource::class) && method_exists(FinanceChargeResource::class, 'getUrl')
            ? FinanceChargeResource::getUrl('index')
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
