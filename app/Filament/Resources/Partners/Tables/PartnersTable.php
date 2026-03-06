<?php

namespace App\Filament\Resources\Partners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path_png')
                    ->label('Logo')
                    ->disk('public_folder') // Předpokládám, že assety jsou v public
                    ->height(40),

                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'main_partner' => 'success',
                        'general_partner' => 'info',
                        'partner' => 'gray',
                        'media_partner' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'main_partner' => 'Hlavní partner',
                        'general_partner' => 'Generální partner',
                        'partner' => 'Partner',
                        'media_partner' => 'Mediální partner',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Zvýrazněný')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ partnera')
                    ->options([
                        'main_partner' => 'Hlavní partner',
                        'general_partner' => 'Generální partner',
                        'partner' => 'Partner',
                        'media_partner' => 'Mediální partner',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Pouze aktivní'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
