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

    protected static ?string $title = 'admin.resources.attendance.plural_label';

    protected static ?string $modelLabel = 'admin.resources.attendance.label';

    protected static ?string $pluralModelLabel = 'admin.resources.attendance.plural_label';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.resources.attendance.plural_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.attendance.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.attendance.plural_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label(__('admin.resources.attendance.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('planned_status')
                    ->label(__('admin.resources.attendance.fields.planned_status'))
                    ->options([
                        'pending' => __('admin.resources.attendance.planned_statuses.pending'),
                        'confirmed' => __('admin.resources.attendance.planned_statuses.confirmed'),
                        'declined' => __('admin.resources.attendance.planned_statuses.declined'),
                        'maybe' => __('admin.resources.attendance.planned_statuses.maybe'),
                    ])
                    ->default('pending')
                    ->required()
                    ->live(),
                Select::make('excuse_reason')
                    ->label(__('admin.resources.attendance.fields.excuse_reason'))
                    ->options(ExcuseReason::class)
                    ->hidden(fn ($get) => $get('planned_status') !== 'declined')
                    ->nullable(),
                Select::make('actual_status')
                    ->label(__('admin.resources.attendance.fields.actual_status'))
                    ->options([
                        'attended' => __('admin.resources.attendance.actual_statuses.attended'),
                        'absent' => __('admin.resources.attendance.actual_statuses.absent'),
                        'excused' => __('admin.resources.attendance.actual_statuses.excused'),
                    ])
                    ->nullable(),
                Textarea::make('note')
                    ->label(__('admin.resources.attendance.fields.note'))
                    ->placeholder(__('admin.resources.attendance.placeholders.note'))
                    ->rows(2),
                Textarea::make('internal_note')
                    ->label(__('admin.resources.attendance.fields.internal_note'))
                    ->placeholder(__('admin.resources.attendance.placeholders.internal_note'))
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
                    ->label(__('admin.resources.attendance.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('planned_status')
                    ->label(__('admin.resources.attendance.fields.planned_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'success',
                        'declined' => 'danger',
                        'maybe' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('admin.resources.attendance.planned_statuses.pending'),
                        'confirmed' => __('admin.resources.attendance.planned_statuses.confirmed'),
                        'declined' => __('admin.resources.attendance.planned_statuses.declined'),
                        'maybe' => __('admin.resources.attendance.planned_statuses.maybe'),
                        default => $state,
                    })
                    ->description(fn (\App\Models\Attendance $record): ?string => $record->excuse_reason?->getLabel())
                    ->sortable(),
                TextColumn::make('actual_status')
                    ->label(__('admin.resources.attendance.fields.actual_status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'attended' => 'success',
                        'absent' => 'danger',
                        'excused' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'attended' => __('admin.resources.attendance.actual_statuses.attended'),
                        'absent' => __('admin.resources.attendance.actual_statuses.absent'),
                        'excused' => __('admin.resources.attendance.actual_statuses.excused'),
                        default => '?',
                    })
                    ->sortable(),
                IconColumn::make('is_mismatch')
                    ->label(__('admin.resources.attendance.fields.is_mismatch'))
                    ->boolean()
                    ->trueIcon(FilamentIcon::get('triangle-exclamation'))
                    ->falseIcon(null)
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('note')
                    ->label(__('admin.resources.attendance.fields.note'))
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('internal_note')
                    ->label(__('admin.resources.attendance.fields.internal_note'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responded_at')
                    ->label(__('admin.resources.attendance.fields.responded_at'))
                    ->dateTime('d.m. H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('planned_status')
                    ->label(__('admin.resources.attendance.fields.planned_status'))
                    ->options([
                        'pending' => __('admin.resources.attendance.planned_statuses.pending'),
                        'confirmed' => __('admin.resources.attendance.planned_statuses.confirmed'),
                        'declined' => __('admin.resources.attendance.planned_statuses.declined'),
                        'maybe' => __('admin.resources.attendance.planned_statuses.maybe'),
                    ]),
                SelectFilter::make('actual_status')
                    ->label(__('admin.resources.attendance.fields.actual_status'))
                    ->options([
                        'attended' => __('admin.resources.attendance.actual_statuses.attended'),
                        'absent' => __('admin.resources.attendance.actual_statuses.absent'),
                        'excused' => __('admin.resources.attendance.actual_statuses.excused'),
                    ]),
                TernaryFilter::make('is_mismatch')
                    ->label(__('admin.resources.attendance.fields.is_mismatch')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin.resources.attendance.actions.add_member')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label(__('user.actions.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
