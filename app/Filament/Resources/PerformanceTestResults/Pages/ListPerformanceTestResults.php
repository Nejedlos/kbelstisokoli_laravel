<?php

namespace App\Filament\Resources\PerformanceTestResults\Pages;

use App\Filament\Resources\PerformanceTestResults\PerformanceTestResultResource;
use App\Services\PerformanceTestService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListPerformanceTestResults extends ListRecords
{
    protected static string $resource = PerformanceTestResultResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PerformanceTestResultResource::getWidgets()[0],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runPublicTest')
                ->label('Test Veřejné sekce')
                ->icon('fa-light fa-globe')
                ->color('success')
                ->action(fn (PerformanceTestService $service) => $this->runTest($service, 'public')),

            Action::make('runMemberTest')
                ->label('Test Členské sekce')
                ->icon('fa-light fa-user')
                ->color('info')
                ->action(fn (PerformanceTestService $service) => $this->runTest($service, 'member')),

            Action::make('runAdminTest')
                ->label('Test Admin sekce')
                ->icon('fa-light fa-lock')
                ->color('warning')
                ->action(fn (PerformanceTestService $service) => $this->runTest($service, 'admin')),

            CreateAction::make(),
        ];
    }

    protected function runTest(PerformanceTestService $service, string $section): void
    {
        $sessionId = session()->getId();
        $service->runSectionTest($section, $sessionId);

        Notification::make()
            ->title('Testování dokončeno')
            ->body("Testy pro sekci {$section} byly úspěšně provedeny pro všechny scénáře.")
            ->success()
            ->send();
    }
}
