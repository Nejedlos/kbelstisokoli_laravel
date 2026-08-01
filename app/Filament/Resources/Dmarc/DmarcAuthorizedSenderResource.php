<?php

namespace App\Filament\Resources\Dmarc;

use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Pages;
use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Schemas\DmarcAuthorizedSenderForm;
use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Tables\DmarcAuthorizedSendersTable;
use App\Models\Dmarc\DmarcAuthorizedSender;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DmarcAuthorizedSenderResource extends Resource
{
    protected static ?string $model = DmarcAuthorizedSender::class;

    protected static ?string $slug = 'dmarc-authorized-senders';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.dmarc_monitor');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return IconHelper::get(IconHelper::DMARC);
    }

    public static function form(Schema $schema): Schema
    {
        return DmarcAuthorizedSenderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DmarcAuthorizedSendersTable::configure($table);
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
            'index' => Pages\ListDmarcAuthorizedSenders::route('/'),
            'create' => Pages\CreateDmarcAuthorizedSender::route('/create'),
            'edit' => Pages\EditDmarcAuthorizedSender::route('/{record}/edit'),
        ];
    }
}
