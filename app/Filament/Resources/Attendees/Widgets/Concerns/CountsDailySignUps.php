<?php

namespace App\Filament\Resources\Attendees\Widgets\Concerns;

use App\Models\Attendee;
use Carbon\CarbonImmutable;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;

trait CountsDailySignUps
{
    /**
     * Count sign-ups per day for the last `$days` days, including days with none.
     *
     * The query is cloned and its ordering cleared so the table's sort
     * doesn't leak into the grouped trend query.
     *
     * @param  Builder<Attendee>  $query
     * @return array<string, int> Sign-up counts keyed by `Y-m-d` date, oldest first.
     */
    protected function dailySignUps(Builder $query, int $days): array
    {
        $today = CarbonImmutable::today();

        return Trend::query((clone $query)->reorder())
            ->dateColumn('attendees.created_at')
            ->between(start: $today->subDays($days - 1), end: $today->endOfDay())
            ->perDay()
            ->count()
            ->mapWithKeys(fn (TrendValue $value): array => [$value->date => (int) $value->aggregate])
            ->all();
    }
}
