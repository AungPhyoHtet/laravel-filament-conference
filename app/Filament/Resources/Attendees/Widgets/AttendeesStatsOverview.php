<?php

namespace App\Filament\Resources\Attendees\Widgets;

use App\Filament\Resources\Attendees\Pages\ListAttendees;
use App\Filament\Resources\Attendees\Widgets\Concerns\CountsDailySignUps;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendeesStatsOverview extends StatsOverviewWidget
{
    use CountsDailySignUps;
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListAttendees::class;
    }

    /**
     * Stats for the attendees matching the table's current search and filters.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();
        $total = (clone $query)->count();
        $conferences = (clone $query)->distinct()->count('attendees.conference_id');

        $weekStart = CarbonImmutable::today()->subDays(6);
        $thisWeek = (clone $query)->where('attendees.created_at', '>=', $weekStart)->count();
        $lastWeek = (clone $query)
            ->where('attendees.created_at', '>=', $weekStart->subDays(7))
            ->where('attendees.created_at', '<', $weekStart)
            ->count();

        return [
            Stat::make('Total attendees', number_format($total))
                ->description('Across '.trans_choice(':count conference|:count conferences', $conferences))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->chart(array_map(floatval(...), array_values($this->dailySignUps($query, 14))))
                ->color('primary'),
            Stat::make('Sign-ups in the last 7 days', number_format($thisWeek))
                ->description($this->weekOverWeekDescription($thisWeek, $lastWeek))
                ->descriptionIcon($thisWeek >= $lastWeek ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($thisWeek >= $lastWeek ? 'success' : 'danger'),
            Stat::make('Average per conference', $conferences > 0 ? number_format($total / $conferences, 1) : '0')
                ->description('Attendees per conference with sign-ups')
                ->descriptionIcon(Heroicon::OutlinedUserGroup),
        ];
    }

    protected function weekOverWeekDescription(int $thisWeek, int $lastWeek): string
    {
        if ($lastWeek === 0) {
            return $thisWeek === 0 ? 'No sign-ups in the previous 7 days either' : 'Up from none the previous 7 days';
        }

        $change = (int) round((($thisWeek - $lastWeek) / $lastWeek) * 100);

        return ($change >= 0 ? "{$change}% increase" : abs($change).'% decrease').' on the previous 7 days';
    }
}
