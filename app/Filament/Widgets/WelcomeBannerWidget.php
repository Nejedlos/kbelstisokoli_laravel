<?php

namespace App\Filament\Widgets;

use App\Models\PlayerProfile;
use App\Models\User;
use Filament\Widgets\Widget;

class WelcomeBannerWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-banner-widget';

    protected int|string|array $columnSpan = 'full';

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

        return [
            'userName' => $userName,
            'activePlayers' => $activePlayers,
            'actions' => [
                'match' => $matchCreate,
                'user' => $userCreate,
                'post' => $postCreate,
            ],
        ];
    }
}
