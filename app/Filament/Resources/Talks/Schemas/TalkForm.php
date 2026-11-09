<?php

namespace App\Filament\Resources\Talks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TalkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('abstract')
                    ->required()
                    ->columnSpanFull(),
                Select::make('speaker_id')
                    ->relationship('speaker', 'name')
                    ->required(),
            ]);
    }
}
