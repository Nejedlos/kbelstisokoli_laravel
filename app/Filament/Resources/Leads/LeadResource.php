<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\ManageLeads;
use App\Models\Lead;
use App\Services\LeadWorkflowService;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content_and_media');
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.lead.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.lead.plural_label');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(IconHelper::ANNOUNCEMENTS);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('leads.fields.status'))->schema([
                Select::make('responsible_user_id')
                    ->label(__('leads.fields.responsible'))
                    ->options(fn () => app(LeadWorkflowService::class)->eligibleUsers()->pluck('name', 'id'))
                    ->searchable()
                    ->helperText(__('leads.not_provided').': '.config('leads.technical_contact_email')),
                Select::make('status')->label(__('leads.fields.status'))->options(__('leads.statuses'))->required(),
                DateTimePicker::make('next_contact_at')->label(__('leads.fields.next_contact_at')),
                Textarea::make('internal_notes')->label(__('leads.fields.internal_notes'))->rows(4)->columnSpanFull(),
            ])->columns(2),
            Section::make(__('leads.fields.message'))->schema([
                TextInput::make('name')->label(__('leads.fields.name'))->disabled(),
                TextInput::make('email')->label(__('leads.fields.email'))->disabled()->copyable(),
                TextInput::make('phone')->label(__('leads.fields.phone'))->disabled()->copyable(),
                TextInput::make('subject')->label(__('leads.fields.subject'))->disabled(),
                Textarea::make('message')->label(__('leads.fields.message'))->disabled()->rows(6)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('leads.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('leads.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('leads.types.'.$state)),
                TextColumn::make('status')
                    ->label(__('leads.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('leads.statuses.'.$state))
                    ->color(fn (string $state) => match ($state) {
                        'accepted' => 'success', 'rejected' => 'danger', 'deferred' => 'warning', 'in_progress' => 'info', default => 'gray',
                    }),
                TextColumn::make('responsible.name')
                    ->label(__('leads.fields.responsible'))
                    ->placeholder(__('leads.not_provided')),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('leads.fields.status'))->options(__('leads.statuses')),
                SelectFilter::make('type')->label(__('leads.fields.type'))->options(__('leads.types')),
                SelectFilter::make('responsible_user_id')
                    ->label(__('leads.fields.responsible'))
                    ->options(fn () => app(LeadWorkflowService::class)->eligibleUsers()->pluck('name', 'id')),
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make()
                    ->using(function (Lead $record, array $data): Lead {
                        $responsibleUserId = $data['responsible_user_id'] ?? null;
                        $responsibleChanged = (int) ($record->responsible_user_id ?? 0) !== (int) ($responsibleUserId ?? 0);
                        unset($data['responsible_user_id']);

                        $record->fill($data);
                        if ($record->isDirty('status')) {
                            $record->status_changed_at = now();
                        }
                        $record->save();

                        if ($responsibleChanged) {
                            app(LeadWorkflowService::class)->assign($record, $responsibleUserId);
                        }

                        return $record;
                    }),
                Action::make('in_progress')
                    ->label(__('leads.statuses.in_progress'))
                    ->color('info')
                    ->action(fn (Lead $record) => $record->changeStatus('in_progress')),
                Action::make('accepted')
                    ->label(__('leads.statuses.accepted'))
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Lead $record) => $record->changeStatus('accepted')),
                Action::make('rejected')
                    ->label(__('leads.statuses.rejected'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Lead $record) => $record->changeStatus('rejected')),
                Action::make('deferred')
                    ->label(__('leads.statuses.deferred'))
                    ->color('warning')
                    ->action(fn (Lead $record) => $record->changeStatus('deferred')),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
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
