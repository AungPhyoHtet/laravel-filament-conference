<?php

namespace App\Filament\Resources\Talks\Schemas;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Concerns\BuildsFormSections;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                self::submissionSection(),
                self::speakerSection(),
            ]);
    }

    public static function detailsSection(): Section
    {
        return self::section('Talk Details', 'The title and abstract of the talk.', Heroicon::OutlinedDocumentText, columns: 1)
            ->schema([
                TextInput::make('title')
                    ->required(),
                RichEditor::make('abstract')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['bulletList', 'orderedList', 'blockquote'],
                        ['undo', 'redo'],
                    ])
                    ->required(),
            ]);
    }

    public static function submissionSection(): Section
    {
        return self::section('Submission', 'Review status, length and whether the talk is new.', Heroicon::OutlinedClipboardDocumentCheck)
            ->schema([
                Select::make('status')
                    ->options(TalkStatus::class)
                    ->default(TalkStatus::Submitted)
                    ->required(),
                Select::make('length')
                    ->options(TalkLength::class)
                    ->default(TalkLength::Normal)
                    ->required(),
                Toggle::make('new_talk')
                    ->label('New talk')
                    ->helperText('Has not been given at another conference.')
                    ->default(true),
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
