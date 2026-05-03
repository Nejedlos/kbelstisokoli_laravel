<?php

namespace App\Filament\Resources\Dmarc;

use App\Filament\Resources\Dmarc\DmarcMailboxResource\Pages;
use App\Filament\Resources\Dmarc\DmarcMailboxResource\Schemas\MailboxForm;
use App\Filament\Resources\Dmarc\DmarcMailboxResource\Tables\MailboxesTable;
use App\Models\Dmarc\DmarcMailbox;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DmarcMailboxResource extends Resource
{
    protected static ?string $model = DmarcMailbox::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.dmarc_monitor');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return IconHelper::get(IconHelper::MAILBOX);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.dmarc_mailbox.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.dmarc_mailbox.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return MailboxForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailboxesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailboxes::route('/'),
            'create' => Pages\CreateMailbox::route('/create'),
            'edit' => Pages\EditMailbox::route('/{record}/edit'),
        ];
    }
}
