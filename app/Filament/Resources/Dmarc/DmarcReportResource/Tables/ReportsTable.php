<?php

namespace App\Filament\Resources\Dmarc\DmarcReportResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Dmarc\DmarcReport;
use Illuminate\Support\Facades\Storage;
use App\Support\IconHelper;
use Illuminate\Support\HtmlString;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('org_name')
                    ->label('Organizace')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain')
                    ->label('Doména')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_start')
                    ->label('Období od')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->label('Období do')
                    ->date()
                    ->sortable(),
                TextColumn::make('records_count')
                    ->label('Záznamů')
                    ->counts('records'),
                TextColumn::make('received_at')
                    ->label('Přijato')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('received_at', 'desc')
            ->actions([
                ViewAction::make(),
                Action::make('download_xml')
                    ->label('Stáhnout XML')
                    ->icon('heroicon-o-document-text')
                    ->action(function (DmarcReport $record) {
                        return Storage::download($record->raw_xml_path, "report-{$record->report_id}.xml");
                    }),
                Action::make('download_human')
                    ->label('Lidská verze')
                    ->icon('heroicon-o-user')
                    ->action(function (DmarcReport $record) {
                        $content = "DMARC Aggregate Report\n";
                        $content .= "=====================\n";
                        $content .= "Organizace: {$record->org_name}\n";
                        $content .= "Doména: {$record->domain}\n";
                        $content .= "Období: {$record->date_start->format('d.m.Y')} - {$record->date_end->format('d.m.Y')}\n\n";

                        $content .= "Záznamy:\n";
                        foreach ($record->records as $r) {
                            $content .= "IP: {$r->source_ip} | DKIM: " . ($r->dkim_aligned ? 'PASS' : 'FAIL');
                            $content .= " | SPF: " . ($r->spf_aligned ? 'PASS' : 'FAIL');
                            $content .= " | Počet: {$r->count}\n";
                            if ($r->status !== 'OK') {
                                $content .= "  Doporučení: {$r->recommended_action}\n";
                            }
                            $content .= "-----------------------------------\n";
                        }

                        return response()->streamDownload(function () use ($content) {
                            echo $content;
                        }, "dmarc-human-{$record->report_id}.txt");
                    }),
            ]);
    }
}
