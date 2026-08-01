<?php

namespace App\Filament\Resources\Dmarc;

use App\Filament\Resources\Dmarc\DmarcReportResource\Pages;
use App\Filament\Resources\Dmarc\DmarcReportResource\Schemas\ReportForm;
use App\Filament\Resources\Dmarc\DmarcReportResource\Tables\ReportsTable;
use App\Models\Dmarc\DmarcReport;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DmarcReportResource extends Resource
{
    protected static ?string $model = DmarcReport::class;

    protected static ?string $slug = 'dmarc-reports';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.dmarc_monitor');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return IconHelper::get(IconHelper::DMARC);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.dmarc_report.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.dmarc_report.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}
