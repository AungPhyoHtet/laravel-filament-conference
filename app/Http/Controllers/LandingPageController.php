<?php

namespace App\Http\Controllers;

use App\Enums\TalkStatus;
use App\Models\Conference;
use App\Models\Speaker;
use App\Models\Talk;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class LandingPageController extends Controller
{
    /**
     * Show the public marketing page for published upcoming and ongoing conferences.
     */
    public function __invoke(): View
    {
        $conferences = Conference::query()
            ->listed()
            ->with(['venue.media'])
            ->withCount([
                'speakers',
                'talks' => fn (Builder $query): Builder => $query->where('status', TalkStatus::Approved),
            ])
            ->limit(7)
            ->get();

        $speakers = Speaker::query()
            ->whereHas('conferences', fn (Builder $query): Builder => $query->listed())
            ->withCount(['talks' => fn (Builder $query): Builder => $query->where('status', TalkStatus::Approved)])
            ->orderByDesc('talks_count')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $talks = Talk::query()
            ->where('status', TalkStatus::Approved)
            ->whereHas('conferences', fn (Builder $query): Builder => $query->listed())
            ->with('speaker')
            ->latest()
            ->limit(3)
            ->get();

        return view('landing', [
            'featured' => $conferences->first(),
            'conferences' => $conferences->skip(1)->values(),
            'speakers' => $speakers,
            'talks' => $talks,
            'stats' => [
                'Conferences' => Conference::query()->listed()->count(),
                'Speakers' => Speaker::query()->whereHas('conferences', fn (Builder $query): Builder => $query->listed())->count(),
                'Approved talks' => Talk::query()->where('status', TalkStatus::Approved)->whereHas('conferences', fn (Builder $query): Builder => $query->listed())->count(),
                'Cities' => Conference::query()->listed()->join('venues', 'venues.id', '=', 'conferences.venue_id')->distinct()->count('venues.city'),
            ],
        ]);
    }
}
