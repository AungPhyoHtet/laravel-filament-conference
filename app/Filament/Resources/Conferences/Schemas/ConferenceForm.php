<?php

namespace App\Filament\Resources\Conferences\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                TextInput::make('region')
                    ->required()
                    ->maxLength(255),
                Select::make('venue_id')
                    ->relationship('venue', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
