<?php

namespace App\Filament\Resources\Venues\Schemas;

use App\Enums\Region;
use App\Filament\Concerns\BuildsFormSections;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class VenueForm
{
    use BuildsFormSections;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::detailsSection(),
                self::addressSection(),
                self::imagesSection(),
            ]);
    }

    protected static function detailsSection(): Section
    {
        return self::section('Venue Details', 'The name of the venue.', Heroicon::OutlinedBuildingOffice2, columns: 1)
            ->schema([
                TextInput::make('name')
                    ->required(),
            ]);
    }

    protected static function addressSection(): Section
    {
        return self::section('Address', 'Where the venue is located.', Heroicon::OutlinedMapPin)
            ->schema([
                TextInput::make('city')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                Select::make('region')
                    ->options(Region::class)
                    ->required(),
                TextInput::make('postal_code')
                    ->required(),
            ]);
    }

    protected static function imagesSection(): Section
    {
        return self::section('Images', 'Photos of the venue.', Heroicon::OutlinedPhoto, columns: 1)
            ->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->hiddenLabel()
                    ->collection('venue-images')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor(),
            ]);
    }
}
