<?php

namespace Database\Factories;

use App\Models\CalendarEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEntry>
 */
class CalendarEntryFactory extends Factory
{
    protected $model = CalendarEntry::class;

    public function definition(): array
    {
        $date = CarbonImmutable::instance(
            fake()->dateTimeBetween('-2 months', '+2 months'),
        )->startOfDay();

        return [
            'entry_date' => $date->toDateString(),
            'title' => fake()->randomElement([
                'Client check-in',
                'Invoice follow-up',
                'Content review',
                'Deployment window',
                'Planning block',
                'Research session',
                'Support backlog review',
                'Weekly wrap-up',
            ]),
            'details' => fake()->boolean(65) ? fake()->sentence() : null,
            'source_type' => null,
            'source_id' => null,
        ];
    }

    public function onDate(CarbonImmutable|string $date): static
    {
        $resolvedDate = $date instanceof CarbonImmutable
            ? $date
            : CarbonImmutable::parse($date);

        return $this->state(fn () => [
            'entry_date' => $resolvedDate->toDateString(),
        ]);
    }
}
