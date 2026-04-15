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
        $titles = [
            'Client check-in',
            'Invoice follow-up',
            'Content review',
            'Deployment window',
            'Planning block',
            'Research session',
            'Support backlog review',
            'Weekly wrap-up',
        ];

        $details = [
            'Reviewed current progress and captured the next follow-up action.',
            'Summarized the latest findings and queued the remaining tasks.',
            'Checked status, logged blockers, and noted the next update window.',
            null,
        ];

        $date = CarbonImmutable::today()->addDays(random_int(-60, 60));

        return [
            'entry_date' => $date->toDateString(),
            'title' => $titles[array_rand($titles)],
            'details' => $details[array_rand($details)],
            'follow_up_enabled' => false,
            'follow_up_days' => null,
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
