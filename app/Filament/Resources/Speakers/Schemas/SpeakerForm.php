<?php

namespace App\Filament\Resources\Speakers\Schemas;

use App\Enums\SpeakerQualification;
use App\Filament\Concerns\BuildsFormSections;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SpeakerForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::profileSection(),
                self::qualificationsSection(),
            ]);
    }

    protected static function profileSection(): Section
    {
        return self::section('Profile', 'Who the speaker is and how to reach them.', Heroicon::OutlinedUser)
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('twitter_handle')
                    ->required(),
                Textarea::make('bio')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    protected static function qualificationsSection(): Section
    {
        return self::section('Qualifications', 'What makes this speaker stand out.', Heroicon::OutlinedAcademicCap, columns: 1)
            ->schema([
                CheckboxList::make('qualifications')
                    ->hiddenLabel()
                    ->options(SpeakerQualification::class)
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable(),
            ]);
    }
}
