<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    // App\Providers\FolioServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    // App\Providers\HorizonServiceProvider::class,
    // App\Providers\TelescopeServiceProvider::class,
    // App\Providers\VoltServiceProvider::class,
    Filament\Actions\ActionsServiceProvider::class,
    Filament\FilamentServiceProvider::class,
    Filament\Forms\FormsServiceProvider::class,
    Filament\Infolists\InfolistsServiceProvider::class,
    Filament\Notifications\NotificationsServiceProvider::class,
    Filament\Schemas\SchemasServiceProvider::class,
    Filament\Support\SupportServiceProvider::class,
    Filament\Tables\TablesServiceProvider::class,
    Filament\Widgets\WidgetsServiceProvider::class,
];
