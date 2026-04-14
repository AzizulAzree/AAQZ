<?php

namespace Tests\Unit;

use App\Support\Calendar\CalendarMonth;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class CalendarMonthTest extends TestCase
{
    public function test_calendar_grid_aligns_days_with_sunday_start(): void
    {
        $calendar = CalendarMonth::fromMonthString(
            month: '2026-05',
            today: CarbonImmutable::parse('2026-05-15'),
        );

        $firstWeekDates = array_map(
            fn (array $day) => $day['date']->toDateString(),
            $calendar->weeks[0],
        );

        $this->assertSame([
            '2026-04-26',
            '2026-04-27',
            '2026-04-28',
            '2026-04-29',
            '2026-04-30',
            '2026-05-01',
            '2026-05-02',
        ], $firstWeekDates);

        $this->assertFalse($calendar->weeks[0][0]['is_current_month']);
        $this->assertTrue($calendar->weeks[0][5]['is_current_month']);
    }

    public function test_calendar_attaches_entries_to_the_matching_day(): void
    {
        $calendar = CalendarMonth::fromMonthString(
            month: '2026-04',
            today: CarbonImmutable::parse('2026-04-10'),
        )->withEntries(collect([
            [
                'date' => CarbonImmutable::parse('2026-04-10'),
                'title' => 'Planning session',
                'details' => null,
                'source_type' => null,
                'source_id' => null,
            ],
        ]));

        $weekWithTenth = collect($calendar->weeks)
            ->first(fn (array $week) => collect($week)->contains(
                fn (array $day) => $day['date']->toDateString() === '2026-04-10',
            ));

        $day = collect($weekWithTenth)->first(
            fn (array $item) => $item['date']->toDateString() === '2026-04-10',
        );

        $this->assertNotNull($day);
        $this->assertTrue($day['is_today']);
        $this->assertCount(1, $day['entries']);
        $this->assertSame('Planning session', $day['entries']->first()['title']);
    }
}
