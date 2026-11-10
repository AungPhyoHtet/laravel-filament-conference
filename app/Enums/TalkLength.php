<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TalkLength: string implements HasLabel
{
    case Lightning = 'lightning';
    case Normal = 'normal';
    case Keynote = 'keynote';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lightning => 'Lightning Talk - 15 Minutes',
            self::Normal => 'Normal - 30 Minutes',
            self::Keynote => 'Keynote',
        };
    }
}
