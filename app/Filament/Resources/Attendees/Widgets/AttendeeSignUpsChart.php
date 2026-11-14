<?php

namespace App\Filament\Resources\Attendees\Widgets;

use App\Filament\Resources\Attendees\Pages\ListAttendees;
use App\Filament\Resources\Attendees\Widgets\Concerns\CountsDailySignUps;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class AttendeeSignUpsChart extends ChartWidget
{
    use CountsDailySignUps;
    use InteractsWithPageTable;

    /**
     * Selectable chart ranges, in days.
     *
     * @var array<int, string>
     */
    public const array RANGES = [
        7 => 'Last 7 days',
        30 => 'Last 30 days',
        90 => 'Last 90 days',
    ];

    protected ?string $heading = 'Sign-ups';

    protected ?string $description = 'Daily attendee sign-ups for the attendees shown in the table.';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getTablePage(): string
    {
        return ListAttendees::class;
    }

    /**
     * @return array<int, string>
     */
    protected function getFilters(): array
    {
        return self::RANGES;
    }

    /**
     * @return array{datasets: array<int, array<string, mixed>>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        $days = array_key_exists((int) $this->filter, self::RANGES) ? (int) $this->filter : 30;
        $signUps = $this->dailySignUps($this->getPageTableQuery(), $days);

        return [
            'datasets' => [
                [
                    'label' => 'Sign-ups',
                    'data' => array_values($signUps),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => array_map(
                fn (string $day): string => CarbonImmutable::parse($day)->format('M j'),
                array_keys($signUps),
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
