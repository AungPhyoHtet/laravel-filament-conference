<?php

namespace App\Filament\Resources\Conferences\Schemas;

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use App\Filament\Resources\Venues\Schemas\VenueForm;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ConferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Conference Name')
                    ->default('My Conference')
                    ->hint('Enter the name of the conference.')
                    ->hintIcon('heroicon-o-information-circle')
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
                Select::make('status')
                    ->searchable()
                    ->options(ConferenceStatus::class)
                    ->default(ConferenceStatus::Draft)
                    ->required(),
                Toggle::make('is_published')
                    ->extraFieldWrapperAttributes([
                        'style' => 'margin-top: 2.2rem;',
                    ])
                    ->label('Published')
                    ->default(false),
                Select::make('region')
                    ->options(Region::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('venue_id', null)),
                Select::make('venue_id')
                    ->relationship(
                        'venue',
                        'name',
                        modifyQueryUsing: function (Builder $query, Get $get): Builder {
                            $region = $get('region');

                            if ($region instanceof Region) {
                                $region = $region->value;
                            }

                            return $query->when(
                                $region,
                                fn (Builder $query) => $query->where('region', $region),
                            );
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->createOptionForm(fn (Schema $schema): Schema => VenueForm::configure($schema))
                    ->editOptionForm(fn (Schema $schema): Schema => VenueForm::configure($schema)),
                Select::make('speakers')
                    ->relationship('speakers', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
