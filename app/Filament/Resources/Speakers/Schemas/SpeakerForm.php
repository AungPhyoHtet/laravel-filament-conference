<?php

namespace App\Filament\Resources\Speakers\Schemas;

use App\Enums\SpeakerQualification;
use App\Enums\UserRole;
use App\Filament\Concerns\BuildsFormSections;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class SpeakerForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::profileSection(),
                self::qualificationsSection(),
                self::accountSection(),
            ]);
    }

    /**
     * Linking a speaker-role account lets that person sign in to the speaker panel as this speaker.
     */
    protected static function accountSection(): Section
    {
        return self::section('Account', 'The speaker panel account that manages this profile.', Heroicon::OutlinedKey, columns: 1)
            ->schema([
                Select::make('user_id')
                    ->label('User account')
                    ->relationship(
                        'user',
                        'email',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('role', UserRole::Speaker),
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?int $state): void {
                        $account = User::query()->find($state);

                        if ($account !== null) {
                            $set('name', $account->name);
                            $set('email', $account->email);
                        }
                    })
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'This account already manages another speaker profile.',
                    ]),
            ]);
    }

    protected static function profileSection(): Section
    {
        return self::section('Profile', 'Who the speaker is and how to reach them.', Heroicon::OutlinedUser)
            ->schema([
                self::avatarField(),
                TextInput::make('name')
                    ->disabled(fn (Get $get): bool => filled($get('user_id')))
                    ->helperText(fn (Get $get): ?string => filled($get('user_id')) ? 'Comes from the linked account.' : null)
                    ->required(fn (Get $get): bool => blank($get('user_id'))),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->disabled(fn (Get $get): bool => filled($get('user_id')))
                    ->helperText(fn (Get $get): ?string => filled($get('user_id')) ? 'Comes from the linked account.' : null)
                    ->required(fn (Get $get): bool => blank($get('user_id'))),
                self::twitterHandleField(),
                self::bioField(),
            ]);
    }

    public static function avatarField(): FileUpload
    {
        return FileUpload::make('avatar')
            ->avatar()
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory('avatars')
            ->maxSize(10240)
            ->columnSpanFull();
    }

    public static function twitterHandleField(): TextInput
    {
        return TextInput::make('twitter_handle')
            ->required();
    }

    public static function bioField(): RichEditor
    {
        return RichEditor::make('bio')
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'link'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['undo', 'redo'],
            ])
            ->required()
            ->columnSpanFull();
    }

    public static function qualificationsSection(): Section
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
