<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Models\Speaker;
use App\Models\Talk;

it('lists only the talks given by a speaker', function (): void {
    $speaker = Speaker::factory()->withTalks(2)->create();
    Talk::factory()->create();

    expect($speaker->talks)->toHaveCount(2)
        ->each(fn ($talk) => $talk->speaker->is($speaker)->toBeTrue());
});

it('defaults a new talk to submitted, normal length and new', function (): void {
    $talk = Talk::query()->create([
        'title' => 'Intro to Filament',
        'abstract' => 'Building admin panels.',
        'speaker_id' => Speaker::factory()->create()->getKey(),
    ])->fresh();

    expect($talk->new_talk)->toBeTrue()
        ->and($talk->status)->toBe(TalkStatus::Submitted)
        ->and($talk->length)->toBe(TalkLength::Normal);
});
