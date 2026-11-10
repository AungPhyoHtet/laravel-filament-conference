<?php

namespace App\Filament\Resources\Conferences\Schemas;

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use App\Filament\Concerns\BuildsFormSections;
use App\Filament\Resources\Venues\Schemas\VenueForm;
use App\Models\Conference;
use App\Models\Venue;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ConferenceForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::detailsSection(),
                self::locationSection(),
                self::factoryFillActions(),
            ]);
    }

    /**
     * Local-only helper that fills the create form with factory data, reusing an existing venue.
     */
    protected static function factoryFillActions(): Actions
    {
        return Actions::make([
            Action::make('fillWithFactoryData')
                ->label('Fill with Factory Data')
                ->icon(Heroicon::OutlinedStar)
                ->visible(fn (string $operation): bool => $operation === 'create' && app()->isLocal())
                ->action(fn (Actions $schemaComponent): Schema => $schemaComponent->getRootContainer()->fill(
                    Conference::factory()
                        ->make(['venue_id' => Venue::query()->inRandomOrder()->value('id')])
                        ->toArray(),
                )),
        ])->key('factoryFillActions');
    }

    protected static function detailsSection(): Section
    {
        return self::section('Conference Details', 'The name, schedule, status and speakers of the conference.', Heroicon::OutlinedInformationCircle)
            ->columns([
                'md' => 2,
                'lg' => 3,
            ])
            ->schema([
                TextInput::make('name')
                    ->label('Conference Name')
                    ->default('My Conference')
                    ->hint('Enter the name of the conference.')
                    ->hintIcon(Heroicon::OutlinedInformationCircle)
                    ->helperText('The name of the conference.')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                MarkdownEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->native(false)
                    ->required()
                    ->afterOrEqual('start_date'),
                self::statusFieldset(),
                self::speakersSelect(),
            ]);
    }

    protected static function statusFieldset(): Fieldset
    {
        return Fieldset::make('Status')
            ->columnSpanFull()
            ->schema([
                Select::make('status')
                    ->options(ConferenceStatus::class)
                    ->default(ConferenceStatus::Draft)
                    ->searchable()
                    ->required(),
                Toggle::make('is_published')
                    ->label('Published')
                    ->default(false)
                    ->extraFieldWrapperAttributes(['style' => 'margin-top: 2.2rem;']),
            ]);
    }

    protected static function locationSection(): Section
    {
        return self::section('Location', 'The region and venue hosting the conference.', Heroicon::OutlinedMapPin)
            ->schema([
                Select::make('region')
                    ->options(Region::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('venue_id', null)),
                self::venueSelect(),
            ]);
    }

    protected static function speakersSelect(): Select
    {
        return Select::make('speakers')
            ->relationship('speakers', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->columnSpanFull();
    }

    /**
     * Venue select limited to venues in the selected region, with inline create and edit.
     */
    protected static function venueSelect(): Select
    {
        return Select::make('venue_id')
            ->relationship(
                'venue',
                'name',
                modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::scopeVenuesToRegion($query, $get('region')),
            )
            ->searchable()
            ->preload()
            ->createOptionForm(fn (Schema $schema): Schema => VenueForm::configure($schema))
            ->editOptionForm(fn (Schema $schema): Schema => VenueForm::configure($schema));
    }

    /**
     * @param  Builder<Venue>  $query
     * @return Builder<Venue>
     */
    protected static function scopeVenuesToRegion(Builder $query, Region|string|null $region): Builder
    {
        $region = $region instanceof Region ? $region : Region::tryFrom((string) $region);

        return $query->when($region, fn (Builder $query, Region $region): Builder => $query->where('region', $region));
    }
}
