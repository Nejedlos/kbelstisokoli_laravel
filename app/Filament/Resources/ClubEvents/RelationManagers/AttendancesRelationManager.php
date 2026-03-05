<?php

namespace App\Filament\Resources\ClubEvents\RelationManagers;

use App\Enums\ExcuseReason;
use App\Support\FilamentIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Docházka / RSVP';

    protected static ?string $modelLabel = 'Záznam docházky';

    protected static ?string $pluralModelLabel = 'Záznamy docházky';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Člen')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('planned_status')
                    ->label('RSVP (Plánováno)')
                    ->options([
                        'pending' => 'Čeká na vyjádření',
                        'confirmed' => 'Potvrzeno (Přijde)',
                        'declined' => 'Omluveno (Nepřijde)',
                        'maybe' => 'Možná',
                    ])
                    ->default('pending')
                    ->required()
                    ->live(),
                Select::make('excuse_reason')
                    ->label('Důvod omluvy')
                    ->options(ExcuseReason::class)
                    ->hidden(fn ($get) => $get('planned_status') !== 'declined')
                    ->nullable(),
                Select::make('actual_status')
                    ->label('Realita (Trenér)')
                    ->options([
                        'attended' => 'Přítomen',
                        'absent' => 'Nepřítomen (neomluven)',
                        'excused' => 'Omluven (trenérem)',
                    ])
                    ->nullable(),
                Textarea::make('note')
                    ->label('Poznámka člena / Důvod omluvenky')
                    ->placeholder('Zadáno členem...')
                    ->rows(2),
                Textarea::make('internal_note')
                    ->label('Interní poznámka (pro trenéry)')
                    ->placeholder('Zadáno trenérem...')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->recordClasses(fn (\App\Models\Attendance $record) => $record->is_mismatch ? 'bg-danger-50 dark:bg-danger-900/20' : null)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Člen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('planned_status')
                    ->label('RSVP')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'success',
                        'declined' => 'danger',
                        'maybe' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Čeká',
                        'confirmed' => 'Přijde',
                        'declined' => 'Nepřijde',
                        'maybe' => 'Možná',
                        default => $state,
                    })
                    ->description(fn (\App\Models\Attendance $record): ?string => $record->excuse_reason?->getLabel())
                    ->sortable(),
                TextColumn::make('actual_status')
                    ->label('Realita')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'attended' => 'success',
                        'absent' => 'danger',
                        'excused' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'attended' => 'Byl',
                        'absent' => 'Nebyl',
                        'excused' => 'Omluven',
                        default => '?',
                    })
                    ->sortable(),
                IconColumn::make('is_mismatch')
                    ->label('Mismatch')
                    ->boolean()
                    ->trueIcon(FilamentIcon::get('triangle-exclamation'))
                    ->falseIcon(null)
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Poznámka')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('internal_note')
                    ->label('Interní pozn.')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responded_at')
                    ->label('Odpovězeno')
                    ->dateTime('d.m. H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('planned_status')
                    ->label('Stav RSVP')
                    ->options([
                        'pending' => 'Čeká',
                        'confirmed' => 'Přijde',
                        'declined' => 'Nepřijde',
                        'maybe' => 'Možná',
                    ]),
                SelectFilter::make('actual_status')
                    ->label('Stav Realita')
                    ->options([
                        'attended' => 'Byl',
                        'absent' => 'Nebyl',
                        'excused' => 'Omluven',
                    ]),
                TernaryFilter::make('is_mismatch')
                    ->label('Pouze mismatch'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Přidat člena ručně'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Odebrat'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
