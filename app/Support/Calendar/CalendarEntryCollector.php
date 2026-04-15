<?php

namespace App\Support\Calendar;

use App\Models\CalendarEntry;
use App\Models\User;
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
        $entries = CalendarEntry::query()
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($followUpQuery): void {
                        $followUpQuery
                            ->where('follow_up_enabled', true)
                            ->whereNotNull('follow_up_days');
                    });
            })
            ->orderBy('entry_date')
            ->orderBy('title')
            ->get();

        $owners = User::query()
            ->whereIn('id', $entries
                ->filter(fn (CalendarEntry $entry) => $entry->source_type === 'self' && $entry->source_id !== null)
                ->pluck('source_id')
                ->unique()
                ->values())
            ->get()
            ->keyBy('id');

        return $entries
            ->flatMap(function (CalendarEntry $entry) use ($owners, $start, $end): array {
                $owner = $entry->source_type === 'self' && $entry->source_id !== null
                    ? $owners->get($entry->source_id)
                    : null;

                $baseEntry = [
                    'id' => $entry->id,
                    'date' => CarbonImmutable::instance($entry->entry_date),
                    'title' => $entry->title,
                    'details' => $entry->details,
                    'is_follow_up' => false,
                    'follow_up_enabled' => (bool) $entry->follow_up_enabled,
                    'follow_up_days' => $entry->follow_up_days,
                    'source_type' => $entry->source_type,
                    'source_id' => $entry->source_id,
                    'owner_name' => $owner?->name,
                    'owner_color' => $owner?->ownerColor(),
                    'created_at' => $entry->created_at?->toImmutable(),
                    'updated_at' => $entry->updated_at?->toImmutable(),
                    'model' => $entry,
                    'tag' => null,
                ];

                $items = [];

                if ($baseEntry['date']->betweenIncluded($start, $end)) {
                    $items[] = $baseEntry;
                }

                if ($entry->follow_up_enabled && $entry->follow_up_days !== null) {
                    $followUpDate = CarbonImmutable::instance($entry->entry_date)->addDays($entry->follow_up_days);

                    if ($followUpDate->betweenIncluded($start, $end)) {
                        $items[] = [
                            ...$baseEntry,
                            'id' => 'follow-up-'.$entry->id.'-'.$followUpDate->toDateString(),
                            'date' => $followUpDate,
                            'is_follow_up' => true,
                            'tag' => 'Follow Up',
                        ];
                    }
                }

                return $items;
            })
            ->values();
    }
}
