<?php

namespace App\Filament\Resources\Conferences\Schemas;

use App\Enums\ConferenceStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Conference Name')
                    ->default('My Conference')
                    ->hint('Enter the name of the conference.')
                    ->hintIcon('heroicon-o-information-circle')
                    ->helperText('The name of the conference.')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                MarkdownEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->native(false)
                    ->required()
                    ->afterOrEqual('start_date'),
                Select::make('status')
                    ->searchable()
                    ->options(ConferenceStatus::class)
                    ->default(ConferenceStatus::Draft)
                    ->required(),
                TextInput::make('region')
                    ->required()
                    ->maxLength(255),
                Select::make('venue_id')
                    ->relationship('venue', 'name')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_published')
                    ->label('Published')
                    ->default(false),
            ]);
    }
}
