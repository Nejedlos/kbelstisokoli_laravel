<?php

namespace App\Filament\Resources\Dmarc\DmarcReportResource\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\ViewField;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metadata reportu')
                    ->columns(3)
                    ->schema([
                        TextInput::make('org_name')
                            ->label('Organizace')
                            ->disabled(),
                        TextInput::make('report_id')
                            ->label('Report ID')
                            ->disabled(),
                        TextInput::make('domain')
                            ->label('Doména')
                            ->disabled(),
                        Placeholder::make('date_start')
                            ->label('Od')
                            ->content(fn ($record) => $record?->date_start?->format('d.m.Y H:i:s') ?? '-'),
                        Placeholder::make('date_end')
                            ->label('Do')
                            ->content(fn ($record) => $record?->date_end?->format('d.m.Y H:i:s') ?? '-'),
                        Placeholder::make('received_at')
                            ->label('Přijato')
                            ->content(fn ($record) => $record?->received_at?->format('d.m.Y H:i:s') ?? '-'),
                    ]),

                Section::make('Záznamy reportu')
                    ->schema([
                        ViewField::make('records_view')
                            ->view('filament.dmarc.report-details')
                            ->columnSpanFull()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
