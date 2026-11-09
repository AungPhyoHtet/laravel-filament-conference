<?php

namespace App\Filament\Resources\Talks\Schemas;

use App\Filament\Concerns\BuildsFormSections;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TalkForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::detailsSection(),
                self::speakerSection(),
            ]);
    }

    protected static function detailsSection(): Section
    {
        return self::section('Talk Details', 'The title and abstract of the talk.', Heroicon::OutlinedDocumentText, columns: 1)
            ->schema([
                TextInput::make('title')
                    ->required(),
                Textarea::make('abstract')
                    ->required(),
            ]);
    }

    protected static function speakerSection(): Section
    {
        return self::section('Speaker', 'Who is giving the talk.', Heroicon::OutlinedMicrophone, columns: 1)
            ->schema([
                Select::make('speaker_id')
                    ->relationship('speaker', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
