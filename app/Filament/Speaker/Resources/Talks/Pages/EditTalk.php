<?php

namespace App\Filament\Speaker\Resources\Talks\Pages;

use App\Filament\Speaker\Resources\Talks\TalkResource;
use Filament\Resources\Pages\EditRecord;

class EditTalk extends EditRecord
{
    protected static string $resource = TalkResource::class;
}
