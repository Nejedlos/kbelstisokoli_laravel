<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\BasketballPosition;
use App\Enums\DominantHand;
use App\Enums\JerseySize;
use App\Models\PlayerProfile;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayerProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'playerProfiles';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Historie hráčských profilů');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Platnost profilu')
                    ->icon(IconHelper::get(IconHelper::CLOCK))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktivní')
                                    ->default(true)
                                    ->onColor('success'),
                                DatePicker::make('valid_from')
                                    ->label('Platnost od')
                                    ->native(false)
                                    ->default(now()),
                                DatePicker::make('valid_to')
                                    ->label('Platnost do')
                                    ->native(false),
                            ]),
                    ]),

                Section::make(__('user.sections.basketball'))
                    ->icon(IconHelper::get(IconHelper::BASKETBALL))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('jersey_number')
                                    ->label(__('user.fields.jersey_number'))
                                    ->numeric(),
                                Select::make('position')
                                    ->label(__('user.fields.position'))
                                    ->options(BasketballPosition::class),
                                Select::make('primary_team_id')
                                    ->label(__('user.fields.primary_team'))
                                    ->relationship('primaryTeam', 'name'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('dominant_hand')
                                    ->label(__('user.fields.dominant_hand'))
                                    ->options(DominantHand::class),
                                TextInput::make('license_number')
                                    ->label(__('user.fields.license_number')),
                            ]),
                    ]),

                Section::make(__('user.sections.physical'))
                    ->icon(IconHelper::get(IconHelper::PHYSICAL))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('height_cm')
                                    ->label(__('user.fields.height_cm'))
                                    ->numeric()
                                    ->suffix('cm'),
                                TextInput::make('weight_kg')
                                    ->label(__('user.fields.weight_kg'))
                                    ->numeric()
                                    ->suffix('kg'),
                                Select::make('jersey_size')
                                    ->label(__('user.fields.jersey_size'))
                                    ->options(JerseySize::class),
                                Select::make('shorts_size')
                                    ->label(__('user.fields.shorts_size'))
                                    ->options(JerseySize::class),
                            ]),
                    ]),

                Section::make(__('user.sections.internal'))
                    ->icon(IconHelper::get(IconHelper::NOTE))
                    ->collapsed()
                    ->schema([
                        Textarea::make('medical_note')
                            ->label(__('user.fields.medical_note'))
                            ->rows(3),
                        Textarea::make('coach_note')
                            ->label(__('user.fields.coach_note'))
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jersey_number')
            ->defaultSort('valid_from', 'desc')
            ->columns([
                TextColumn::make('valid_from')
                    ->label('Od')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('valid_to')
                    ->label('Do')
                    ->date('d.m.Y')
                    ->placeholder('Současnost')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('primaryTeam.name')
                    ->label('Tým')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('jersey_number')
                    ->label('Číslo')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Pozice')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nový profil')
                    ->icon(IconHelper::get(IconHelper::PLUS)),

                Action::make('transfer')
                    ->label('Změnit působení (Transfer)')
                    ->icon(IconHelper::get(IconHelper::REFRESH))
                    ->color('warning')
                    ->modalHeading('Změna působení hráče')
                    ->modalDescription('Tato akce ukončí aktuálně aktivní profil a vytvoří nový s předvyplněnými údaji.')
                    ->modalSubmitActionLabel('Provést transfer')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('transfer_date')
                                    ->label('Datum změny')
                                    ->default(now())
                                    ->required()
                                    ->native(false),
                                Select::make('new_team_id')
                                    ->label('Nový tým')
                                    ->relationship('primaryTeam', 'name')
                                    ->required(),
                                TextInput::make('new_jersey_number')
                                    ->label('Nové číslo dresu')
                                    ->numeric(),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $user = $this->getOwnerRecord();
                        $activeProfile = $user->activePlayerProfile;

                        if ($activeProfile) {
                            // 1. Ukončíme starý profil
                            $activeProfile->update([
                                'valid_to' => $data['transfer_date'],
                                'is_active' => false,
                            ]);
                        }

                        // 2. Vytvoříme nový profil
                        $newProfileData = $activeProfile ? $activeProfile->replicate()->toArray() : [];

                        // Přepíšeme klíčové údaje
                        $newProfileData['user_id'] = $user->id;
                        $newProfileData['primary_team_id'] = $data['new_team_id'];
                        $newProfileData['jersey_number'] = $data['new_jersey_number'] ?? ($activeProfile ? $activeProfile->jersey_number : null);
                        $newProfileData['valid_from'] = $data['transfer_date'];
                        $newProfileData['valid_to'] = null;
                        $newProfileData['is_active'] = true;

                        PlayerProfile::create($newProfileData);

                        Notification::make()
                            ->title('Transfer byl úspěšně proveden')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $this->getOwnerRecord()->activePlayerProfile !== null),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
