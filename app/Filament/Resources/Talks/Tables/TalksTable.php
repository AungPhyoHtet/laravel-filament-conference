<?php

namespace App\Filament\Resources\Talks\Tables;

use App\Models\Talk;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TalksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextInputColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable()
                    ->sortable(),
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
                //
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
