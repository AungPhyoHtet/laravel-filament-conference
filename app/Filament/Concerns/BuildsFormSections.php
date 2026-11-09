<?php

namespace App\Filament\Concerns;

use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

trait BuildsFormSections
{
    /**
     * Build a full-width form section with a heading, description and icon.
     */
    protected static function section(string $heading, string $description, Heroicon $icon, int $columns = 2): Section
    {
        return Section::make($heading)
            ->description($description)
            ->icon($icon)
            ->columns($columns)
            ->columnSpanFull();
    }
}
