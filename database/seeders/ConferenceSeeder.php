<?php

namespace Database\Seeders;

use App\Models\Conference;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class ConferenceSeeder extends Seeder
{
    /**
     * Seed venues, speakers with talks, and conferences linked to them.
     */
    public function run(): void
    {
        $venues = Venue::factory(5)->create();

        $speakers = Speaker::factory(20)->create();

        $speakers->each(fn (Speaker $speaker) => Talk::factory(fake()->numberBetween(1, 3))
            ->for($speaker)
            ->create());

        Conference::factory(8)
            ->recycle($venues)
            ->create()
            ->concat(Conference::factory(4)->listed()->recycle($venues)->create())
            ->each(function (Conference $conference) use ($speakers): void {
                $conferenceSpeakers = $speakers->random(fake()->numberBetween(3, 6));

                $conference->speakers()->attach($conferenceSpeakers);

                $conference->talks()->attach(
                    Talk::query()->whereIn('speaker_id', $conferenceSpeakers->modelKeys())->pluck('id'),
                );
            });
    }
}
