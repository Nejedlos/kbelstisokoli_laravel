<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Models\Lead;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.communication');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.lead.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.lead.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::ANNOUNCEMENTS);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeads::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
