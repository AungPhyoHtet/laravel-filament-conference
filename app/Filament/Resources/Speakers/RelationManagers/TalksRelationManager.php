<?php

namespace App\Filament\Resources\Speakers\RelationManagers;

use App\Enums\TalkStatus;
use App\Filament\Resources\Talks\Schemas\TalkForm;
use App\Filament\Resources\Talks\Tables\TalksTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TalksRelationManager extends RelationManager
{
    protected static string $relationship = 'talks';

    /**
     * Shown on the view page as well as edit pages, since speakers are edited in a slide-over.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * The talk form without its speaker section, as the speaker is the owner record.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TalkForm::detailsSection(),
                TalkForm::submissionSection(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('length')
                    ->sortable(),
                ToggleColumn::make('new_talk')
                    ->label('New')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TalkStatus::class),
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
                ActionGroup::make([
                    TalksTable::approveAction(),
                    TalksTable::rejectAction(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    TalksTable::approveBulkAction(),
                    TalksTable::rejectBulkAction(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
