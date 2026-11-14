<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\SpeakerPanelProvider;

return [
    AppServiceProvider::class,
    AppPanelProvider::class,
    SpeakerPanelProvider::class,
];
