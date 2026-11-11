<?php

namespace App\Filament\Resources\Speakers\Schemas;

use App\Enums\SpeakerQualification;
use App\Filament\Concerns\BuildsFormSections;
use App\Models\Speaker;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SpeakerInfolist
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::profileSection(),
                self::qualificationsSection(),
                self::talksSection(),
                self::conferencesSection(),
            ]);
    }

    protected static function profileSection(): Section
    {
        return self::section('Profile', 'Who the speaker is and how to reach them.', Heroicon::OutlinedUser, columns: 3)
            ->schema([
                ImageEntry::make('avatar')
                    ->hiddenLabel()
                    ->disk('public')
                    ->imageHeight(120)
                    ->circular()
                    ->defaultImageUrl(fn (Speaker $record): string => 'https://ui-avatars.com/api/?'.http_build_query([
                        'name' => $record->name,
                        'background' => '0D8ABC',
                        'color' => 'fff',
                        'size' => 240,
                    ])),
                Group::make([
                    TextEntry::make('name')
                        ->size('lg')
                        ->weight('bold'),
                    TextEntry::make('email')
                        ->label('Email address')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->copyable(),
                    TextEntry::make('twitter_handle')
                        ->label('Twitter')
                        ->icon(Heroicon::OutlinedAtSymbol)
                        ->color('primary')
                        ->url(fn (Speaker $record): ?string => $record->twitterUrl())
                        ->openUrlInNewTab(),
                    TextEntry::make('bio')
                        ->html()
                        ->prose()
                        ->columnSpanFull(),
                ])
                    ->columns(2)
                    ->columnSpan(2),
            ]);
    }

    protected static function qualificationsSection(): Section
    {
        return self::section('Qualifications', 'What makes this speaker stand out.', Heroicon::OutlinedAcademicCap, columns: 1)
            ->schema([
                TextEntry::make('qualifications')
                    ->hiddenLabel()
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SpeakerQualification::tryFrom($state)?->getLabel() ?? $state)
                    ->placeholder('No qualifications listed.'),
            ]);
    }

    protected static function talksSection(): Section
    {
        return self::section('Talks', 'Talks this speaker has submitted.', Heroicon::OutlinedMicrophone, columns: 1)
            ->schema([
                RepeatableEntry::make('talks')
                    ->hiddenLabel()
                    ->table([
                        TableColumn::make('Title'),
                        TableColumn::make('Status'),
                        TableColumn::make('Length'),
                        TableColumn::make('New'),
                    ])
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('length'),
                        IconEntry::make('new_talk')
                            ->boolean(),
                    ])
                    ->placeholder('No talks yet.'),
            ]);
    }

    protected static function conferencesSection(): Section
    {
        return self::section('Conferences', 'Conferences this speaker is booked for.', Heroicon::OutlinedCalendarDays, columns: 1)
            ->schema([
                TextEntry::make('conferences.name')
                    ->hiddenLabel()
                    ->badge()
                    ->placeholder('Not booked for any conferences.'),
            ]);
    }
}
