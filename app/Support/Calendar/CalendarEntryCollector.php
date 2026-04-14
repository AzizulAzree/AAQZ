<?php

namespace App\Support\Calendar;

use App\Models\CalendarEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CalendarEntryCollector
{
    public function forRange(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return collect([
            $this->manualEntriesForRange($start, $end),
            // Additional entry sources can be merged here later.
        ])
            ->collapse()
            ->sortBy([
                fn (array $entry) => $entry['date']->format('Y-m-d'),
                fn (array $entry) => $entry['title'],
            ])
            ->values();
    }

    private function manualEntriesForRange(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return CalendarEntry::query()
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('entry_date')
            ->orderBy('title')
            ->get()
            ->map(function (CalendarEntry $entry): array {
                return [
                    'id' => $entry->id,
                    'date' => CarbonImmutable::instance($entry->entry_date),
                    'title' => $entry->title,
                    'details' => $entry->details,
                    'source_type' => $entry->source_type,
                    'source_id' => $entry->source_id,
                    'model' => $entry,
                ];
            });
    }
}
