<?php

namespace App\Filament\Resources\FeedbackReports\Tables;

use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
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
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.feedback_report.type.{$state}"))
                    ->label(__('admin.resources.feedback_report.fields.type')),

                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? __("admin.resources.feedback_report.severity.{$state}") : '-')
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
                        'bug' => __('admin.resources.feedback_report.type.bug'),
                        'idea' => __('admin.resources.feedback_report.type.idea'),
                        'feedback' => __('admin.resources.feedback_report.type.feedback'),
                    ])
                    ->label(__('admin.resources.feedback_report.fields.type')),
                SelectFilter::make('severity')
                    ->options([
                        'low' => __('admin.resources.feedback_report.severity.low'),
                        'medium' => __('admin.resources.feedback_report.severity.medium'),
                        'high' => __('admin.resources.feedback_report.severity.high'),
                    ])
                    ->label(__('admin.resources.feedback_report.fields.severity')),
                SelectFilter::make('status')
                    ->options([
                        'new' => __('admin.resources.feedback_report.status.new'),
                        'triaging' => __('admin.resources.feedback_report.status.triaging'),
                        'in_progress' => __('admin.resources.feedback_report.status.in_progress'),
                        'resolved' => __('admin.resources.feedback_report.status.resolved'),
                        'wont_fix' => __('admin.resources.feedback_report.status.wont_fix'),
                    ])
                    ->label(__('admin.resources.feedback_report.fields.status')),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
