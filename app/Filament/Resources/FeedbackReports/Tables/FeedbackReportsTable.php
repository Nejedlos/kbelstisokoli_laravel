<?php

namespace App\Filament\Resources\FeedbackReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\FeedbackReport;

class FeedbackReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bug' => 'danger',
                        'idea' => 'success',
                        'feedback' => 'info',
                        default => 'gray',
                    })
                    ->label(__('admin.resources.feedback_report.fields.type')),

                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'info',
                        default => 'gray',
                    })
                    ->label(__('admin.resources.feedback_report.fields.severity')),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->label(__('admin.resources.feedback_report.fields.title')),

                TextColumn::make('user.name')
                    ->searchable()
                    ->label(__('admin.resources.feedback_report.fields.user')),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'triaging' => 'warning',
                        'in_progress' => 'primary',
                        'resolved' => 'success',
                        'wont_fix' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.feedback_report.status.{$state}"))
                    ->label(__('admin.resources.feedback_report.fields.status')),

                TextColumn::make('source_area')
                    ->badge()
                    ->label(__('admin.resources.feedback_report.fields.source_area')),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('admin.resources.feedback_report.fields.created_at')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'bug' => 'Bug',
                        'idea' => 'Nápad',
                        'feedback' => 'Ostatní',
                    ]),
                SelectFilter::make('severity')
                    ->options([
                        'low' => 'Nízká',
                        'medium' => 'Střední',
                        'high' => 'Vysoká',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'new' => 'Nové',
                        'triaging' => 'Prověřování',
                        'in_progress' => 'V řešení',
                        'resolved' => 'Vyřešeno',
                        'wont_fix' => 'Nebude se řešit',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
