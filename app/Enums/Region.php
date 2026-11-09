<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Region: string implements HasLabel
{
    case Africa = 'africa';
    case Asia = 'asia';
    case Europe = 'europe';
    case NorthAmerica = 'north_america';
    case SouthAmerica = 'south_america';
    case Oceania = 'oceania';
    case Online = 'online';

    public function getLabel(): string
    {
        return match ($this) {
            self::Africa => 'Africa',
            self::Asia => 'Asia',
            self::Europe => 'Europe',
            self::NorthAmerica => 'North America',
            self::SouthAmerica => 'South America',
            self::Oceania => 'Oceania',
            self::Online => 'Online',
        };
    }
}
