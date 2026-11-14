<?php

namespace App\Filament\Resources\Attendees\Schemas;

use App\Filament\Concerns\BuildsFormSections;
use App\Models\Conference;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Unique;

class AttendeeForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::attendeeSection(),
                self::conferenceSection(),
            ]);
    }

    protected static function attendeeSection(): Section
    {
        return self::section('Attendee', 'Who is attending and how to reach them.', Heroicon::OutlinedUser)
            ->schema([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('conference_id', $get('conference_id')),
                    )
                    ->validationMessages([
                        'unique' => 'This email address is already signed up for this conference.',
                    ]),
            ]);
    }

    protected static function conferenceSection(): Section
    {
        return self::section('Conference', 'The conference this attendee signed up for.', Heroicon::OutlinedCalendarDays, columns: 1)
            ->schema([
                Select::make('conference_id')
                    ->relationship('conference', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Conference $record): string => "{$record->name} · {$record->dateRange()}")
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
