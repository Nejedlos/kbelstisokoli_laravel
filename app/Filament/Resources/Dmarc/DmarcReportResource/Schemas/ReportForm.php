<?php

namespace App\Filament\Resources\Dmarc\DmarcReportResource\Schemas;

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
                        TextInput::make('date_start')
                            ->label('Od')
                            ->dateTime()
                            ->disabled(),
                        TextInput::make('date_end')
                            ->label('Do')
                            ->dateTime()
                            ->disabled(),
                        TextInput::make('received_at')
                            ->label('Přijato')
                            ->dateTime()
                            ->disabled(),
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
