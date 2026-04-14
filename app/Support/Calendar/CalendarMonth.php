<?php

namespace App\Support\Calendar;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CalendarMonth
{
    /**
     * @param  array<int, string>  $weekdayLabels
     * @param  array<int, array<int, array<string, mixed>>>  $weeks
     */
    public function __construct(
        public readonly CarbonImmutable $month,
        public readonly CarbonImmutable $today,
        public readonly CarbonImmutable $gridStartsAt,
        public readonly CarbonImmutable $gridEndsAt,
        public readonly array $weekdayLabels,
        public readonly array $weeks,
    ) {
    }

    public static function fromMonthString(?string $month, ?CarbonImmutable $today = null): self
    {
        $today ??= CarbonImmutable::today();
        $selectedMonth = self::parseMonth($month, $today);
        $gridStartsAt = $selectedMonth->startOfMonth()->startOfWeek(CarbonInterface::SUNDAY);
        $gridEndsAt = $selectedMonth->endOfMonth()->endOfWeek(CarbonInterface::SATURDAY);

        return new self(
            month: $selectedMonth,
            today: $today,
            gridStartsAt: $gridStartsAt,
            gridEndsAt: $gridEndsAt,
            weekdayLabels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            weeks: self::buildWeeks($gridStartsAt, $gridEndsAt, $selectedMonth, $today, collect()),
        );
    }

    public function withEntries(Collection $entries): self
    {
        return new self(
            month: $this->month,
            today: $this->today,
            gridStartsAt: $this->gridStartsAt,
            gridEndsAt: $this->gridEndsAt,
            weekdayLabels: $this->weekdayLabels,
            weeks: self::buildWeeks($this->gridStartsAt, $this->gridEndsAt, $this->month, $this->today, $entries),
        );
    }

    public function previousMonthQuery(): string
    {
        return $this->month->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonthQuery(): string
    {
        return $this->month->addMonthNoOverflow()->format('Y-m');
    }

    public function heading(): string
    {
        return $this->month->isoFormat('MMMM YYYY');
    }

    public function gridStartsAt(): CarbonImmutable
    {
        return $this->gridStartsAt;
    }

    public function gridEndsAt(): CarbonImmutable
    {
        return $this->gridEndsAt;
    }

    private static function parseMonth(?string $month, CarbonImmutable $today): CarbonImmutable
    {
        if ($month === null || $month === '') {
            return $today->startOfMonth();
        }

        try {
            $parsedMonth = CarbonImmutable::createFromFormat('Y-m', $month);
        } catch (InvalidArgumentException) {
            return $today->startOfMonth();
        }

        if ($parsedMonth === false) {
            return $today->startOfMonth();
        }

        return $parsedMonth->startOfMonth();
    }

    private static function buildWeeks(
        CarbonImmutable $start,
        CarbonImmutable $end,
        CarbonImmutable $selectedMonth,
        CarbonImmutable $today,
        Collection $entries,
    ): array {
        $entriesByDate = $entries->groupBy(fn (array $entry) => $entry['date']->toDateString());
        $weeks = [];
        $currentDay = $start;
        $currentWeek = [];

        while ($currentDay->lte($end)) {
            $currentWeek[] = [
                'date' => $currentDay,
                'is_current_month' => $currentDay->isSameMonth($selectedMonth),
                'is_today' => $currentDay->isSameDay($today),
                'entries' => $entriesByDate->get($currentDay->toDateString(), collect()),
            ];

            if (count($currentWeek) === 7) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            $currentDay = $currentDay->addDay();
        }

        return $weeks;
    }
}
