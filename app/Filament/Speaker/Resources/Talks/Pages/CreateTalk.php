<?php

namespace App\Filament\Speaker\Resources\Talks\Pages;

use App\Enums\TalkStatus;
use App\Filament\Speaker\Resources\Talks\TalkResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateTalk extends CreateRecord
{
    protected static string $resource = TalkResource::class;

    /**
     * New talks belong to the signed-in speaker and always start out waiting for review.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            ...$data,
            'speaker_id' => $user->speaker->getKey(),
            'status' => TalkStatus::Submitted,
        ];
    }
}
