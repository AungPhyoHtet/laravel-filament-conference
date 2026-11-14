<?php

namespace App\Filament\Speaker\Resources\Talks\Tables;

use App\Enums\TalkStatus;
use App\Models\Talk;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TalksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abstract')
                    ->formatStateUsing(fn (Talk $record): string => $record->plainAbstract())
                    ->wrap()
                    ->lineClamp(2),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('length')
                    ->sortable(),
                IconColumn::make('new_talk')
                    ->label('New')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TalkStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
