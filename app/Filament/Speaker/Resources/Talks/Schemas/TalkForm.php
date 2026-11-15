<?php

namespace App\Filament\Speaker\Resources\Talks\Schemas;

use App\Enums\TalkLength;
use App\Filament\Concerns\BuildsFormSections;
use App\Filament\Resources\Talks\Schemas\TalkForm as AdminTalkForm;
use Awcodes\Shout\Components\Shout;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TalkForm
{
    use BuildsFormSections;

    /**
     * Speakers fill in the talk itself; the speaker and review status are set for them.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Shout::make('review-notice')
                    ->heading('The organisers review every talk')
                    ->content('You can change your talk until it is approved or rejected.')
                    ->columnSpanFull(),
                AdminTalkForm::detailsSection(),
                self::formatSection(),
            ]);
    }

    protected static function formatSection(): Section
    {
        return self::section('Format', 'How long the talk is and whether it is new.', Heroicon::OutlinedClock)
            ->schema([
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
}
