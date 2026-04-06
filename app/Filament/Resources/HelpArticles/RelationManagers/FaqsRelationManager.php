<?php

namespace App\Filament\Resources\HelpArticles\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->components([
                        TextInput::make('question.cs')
                            ->label(__('admin.resources.help_faq.fields.question_cs'))
                            ->required(),
                        TextInput::make('question.en')
                            ->label(__('admin.resources.help_faq.fields.question_en'))
                            ->required(),
                    ]),
                Grid::make(2)
                    ->components([
                        Textarea::make('answer.cs')
                            ->label(__('admin.resources.help_faq.fields.answer_cs'))
                            ->required()
                            ->rows(3),
                        Textarea::make('answer.en')
                            ->label(__('admin.resources.help_faq.fields.answer_en'))
                            ->required()
                            ->rows(3),
                    ]),
                TextInput::make('sort_order')
                    ->label(__('admin.resources.help_faq.fields.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('question')
                    ->label(__('admin.resources.help_faq.fields.question_cs'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('question->cs', 'like', "%{$search}%")
                            ->orWhere('question->en', 'like', "%{$search}%");
                    }),
                TextColumn::make('sort_order')
                    ->label(__('admin.resources.help_faq.fields.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
