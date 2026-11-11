<?php

namespace App\Filament\Resources\Talks\Tables;

use App\Enums\TalkLength;
use App\Models\Talk;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TalksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(fn (Action $action): Action => $action->button()->label('Filters'))
            ->columns([
                TextInputColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abstract')
                    ->extraAttributes(['style' => 'min-width: 32rem'])
                    ->wrap()
                    ->lineClamp(2)
                    ->tooltip(fn (Talk $record): string => $record->abstract),
                ImageColumn::make('speaker.avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->imageHeight(40)
                    ->circular()
                    ->defaultImageUrl(fn (Talk $record): string => 'https://ui-avatars.com/api/?'.http_build_query([
                        'name' => $record->speaker->name,
                        'background' => '0D8ABC',
                        'color' => 'fff',
                    ])),
                TextColumn::make('speaker.name')
                    ->searchable(),
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
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('length')
                    ->options(TalkLength::class)
                    ->multiple(),
                SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('new_talk')
                    ->label('New talk'),
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
