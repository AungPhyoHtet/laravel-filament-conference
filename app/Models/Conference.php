<?php

namespace App\Models;

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable $end_date
 */
class Conference extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => ConferenceStatus::class,
            'region' => Region::class,
            'venue_id' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Published conferences that are upcoming or ongoing and have not ended, soonest first.
     *
     * @param  Builder<Conference>  $query
     */
    #[Scope]
    protected function listed(Builder $query): void
    {
        $query
            ->where($query->qualifyColumn('is_published'), true)
            ->whereIn($query->qualifyColumn('status'), [ConferenceStatus::Upcoming, ConferenceStatus::Ongoing])
            ->where($query->qualifyColumn('end_date'), '>=', now())
            ->orderBy($query->qualifyColumn('start_date'));
    }

    /**
     * Human-readable date range, such as "Mar 12 – 14, 2027" or "Dec 30, 2026 – Jan 2, 2027".
     */
    public function dateRange(): string
    {
        $start = $this->start_date;
        $end = $this->end_date;

        if ($start->isSameDay($end)) {
            return $start->format('M j, Y');
        }

        if ($start->isSameMonth($end)) {
            return $start->format('M j') . ' – ' . $end->format('j, Y');
        }

        if ($start->isSameYear($end)) {
            return $start->format('M j') . ' – ' . $end->format('M j, Y');
        }

        return $start->format('M j, Y') . ' – ' . $end->format('M j, Y');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }
}
