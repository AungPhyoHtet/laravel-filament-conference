<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Filament\Concerns\BuildsFormSections;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::accountSection(),
                self::passwordSection(),
            ]);
    }

    protected static function accountSection(): Section
    {
        return self::section('Account', 'Who the user is and how they sign in.', Heroicon::OutlinedUser)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at')
                    ->label('Email verified at'),
                Select::make('role')
                    ->options(UserRole::class)
                    ->default(UserRole::Admin)
                    ->helperText('Admins use /admin. Speakers use /speaker, once they are linked to a speaker profile.')
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                    ->required(),
            ]);
    }

    /**
     * Required when creating a user; on edit, leaving it blank keeps the current password.
     */
    protected static function passwordSection(): Section
    {
        return self::section('Password', 'Leave blank when editing to keep the current password.', Heroicon::OutlinedLockClosed)
            ->schema([
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->rule(Password::default())
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Confirm password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
            ]);
    }
}
