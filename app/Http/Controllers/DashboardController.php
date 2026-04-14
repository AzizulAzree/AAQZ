<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEntryRequest;
use App\Models\CalendarEntry;
use App\Support\Calendar\CalendarEntryCollector;
use App\Support\Calendar\CalendarMonth;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, CalendarEntryCollector $entryCollector): View
    {
        $calendar = CalendarMonth::fromMonthString($request->string('month')->toString());
        $createEntryDate = $request->old('entry_date');
        $reminderStart = $calendar->today->startOfDay();
        $reminderEnd = $reminderStart->addDays(2);
        $reminderEntries = $entryCollector->forRange($reminderStart, $reminderEnd)
            ->groupBy(fn (array $entry) => $entry['date']->toDateString());

        return view('dashboard', [
            'calendar' => $calendar->withEntries(
                $entryCollector->forRange($calendar->gridStartsAt(), $calendar->gridEndsAt()),
            ),
            'reminderDays' => collect(range(0, 2))
                ->map(function (int $offset) use ($reminderStart, $reminderEntries): array {
                    $date = $reminderStart->addDays($offset);

                    return [
                        'date' => $date,
                        'label' => match ($offset) {
                            0 => __('Today'),
                            1 => __('Tomorrow'),
                            default => $date->isoFormat('dddd'),
                        },
                        'entries' => $reminderEntries->get($date->toDateString(), collect()),
                    ];
                })
                ->all(),
            'selectedMonthQuery' => $calendar->month->format('Y-m'),
            'createEntryDate' => $createEntryDate,
            'createEntryLabel' => $createEntryDate
                ? CarbonImmutable::parse($createEntryDate)->isoFormat('dddd, D MMMM YYYY')
                : null,
        ]);
    }

    public function store(StoreCalendarEntryRequest $request): RedirectResponse
    {
        CalendarEntry::create([
            'entry_date' => $request->date('entry_date')->toDateString(),
            'title' => $request->string('title')->toString(),
            'details' => $request->filled('details') ? $request->string('details')->toString() : null,
            'source_type' => 'self',
            'source_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('dashboard', array_filter([
                'month' => $request->string('month')->toString(),
            ]))
            ->with('status', 'calendar-entry-created');
    }
}
