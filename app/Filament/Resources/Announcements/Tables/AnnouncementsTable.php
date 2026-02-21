<?php

namespace App\Filament\Resources\Announcements\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Štítek')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'warning' => 'warning',
                        'success' => 'success',
                        default => 'info',
                    })
                    ->searchable(),
                TextColumn::make('message')
                    ->label('Zpráva')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('audience')
                    ->label('Publikum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Veřejnost',
                        'member' => 'Členové',
                        'both' => 'Všichni',
                        default => $state,
                    })
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('Priorita')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Od')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ends_at')
                    ->label('Do')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktivní'),
                SelectFilter::make('audience')
                    ->label('Publikum')
                    ->options([
                        'public' => 'Veřejnost',
                        'member' => 'Členové',
                        'both' => 'Všichni',
                    ]),
                SelectFilter::make('style_variant')
                    ->label('Styl')
                    ->options([
                        'info' => 'Info',
                        'success' => 'Úspěch',
                        'warning' => 'Varování',
                        'urgent' => 'Urgentní',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}
