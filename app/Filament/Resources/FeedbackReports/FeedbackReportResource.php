<?php

namespace App\Filament\Resources\FeedbackReports;

use App\Filament\Resources\FeedbackReports\Pages\CreateFeedbackReport;
use App\Filament\Resources\FeedbackReports\Pages\EditFeedbackReport;
use App\Filament\Resources\FeedbackReports\Pages\ListFeedbackReports;
use App\Filament\Resources\FeedbackReports\Schemas\FeedbackReportForm;
use App\Filament\Resources\FeedbackReports\Tables\FeedbackReportsTable;
use App\Models\FeedbackReport;
use BackedEnum;
use App\Support\AppIcon;
use App\Support\FilamentIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FeedbackReportResource extends Resource
{
    protected static ?string $model = FeedbackReport::class;

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::get(AppIcon::BUG);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.feedback_report.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.feedback_report.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return FeedbackReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedbackReports::route('/'),
            'create' => CreateFeedbackReport::route('/create'),
            'edit' => EditFeedbackReport::route('/{record}/edit'),
        ];
    }
}
