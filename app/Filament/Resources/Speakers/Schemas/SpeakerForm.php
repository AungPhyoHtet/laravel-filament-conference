<?php

namespace App\Filament\Resources\Speakers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SpeakerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Textarea::make('bio')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('twitter_handle')
                    ->required(),
            ]);
    }
}
