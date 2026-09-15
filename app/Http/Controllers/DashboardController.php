<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEntryRequest;
use App\Models\CalendarEntry;
use App\Support\Calendar\CalendarEntryCollector;
use App\Support\Calendar\CalendarMonth;
use App\Support\Calendar\MalaysiaHolidays;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function holidays(Request $request, MalaysiaHolidays $holidays): JsonResponse
    {
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100']]);

        return response()->json($holidays->forYear((int) $data['year']));
    }

    public function index(Request $request, CalendarEntryCollector $entryCollector): View
    {
        $calendar = CalendarMonth::fromMonthString($request->string('month')->toString());
        $createEntryDate = $request->old('entry_date');
        $reminderStart = $calendar->today->startOfDay();
        $reminderEnd = $reminderStart->addDays(2);
        $reminderDisplayDates = collect(range(0, 2))
            ->map(fn (int $offset) => $reminderStart->addDays($offset));
        $reminderFetchDates = $reminderDisplayDates
            ->flatMap(function (CarbonImmutable $date): array {
                $dates = [$date];

                if ($date->dayOfWeekIso === 6) {
                    $dates[] = $date->addDay();
                }

                if ($date->dayOfWeekIso === 7) {
                    $dates[] = $date->subDay();
                }

                return $dates;
            })
            ->unique(fn (CarbonImmutable $date) => $date->toDateString())
            ->sortBy(fn (CarbonImmutable $date) => $date->toDateString())
            ->values();
        $reminderEntries = $entryCollector->forRange($reminderFetchDates->first() ?? $reminderStart, $reminderFetchDates->last() ?? $reminderEnd)
            ->groupBy(fn (array $entry) => $entry['date']->toDateString());
        $reminderDays = $this->buildReminderDays($reminderDisplayDates, $reminderEntries);

        return view('dashboard', [
            'calendar' => $calendar->withEntries(
                $entryCollector->forRange($calendar->gridStartsAt(), $calendar->gridEndsAt()),
            ),
            'reminderDays' => $reminderDays,
            'selectedMonthQuery' => $calendar->month->format('Y-m'),
            'createEntryDate' => $createEntryDate,
            'createEntryLabel' => $createEntryDate
                ? CarbonImmutable::parse($createEntryDate)->isoFormat('dddd, D MMMM YYYY')
                : null,
            'createFollowUpEnabled' => (bool) old('follow_up_enabled', false),
            'createFollowUpDays' => old('follow_up_days'),
        ]);
    }

    public function store(StoreCalendarEntryRequest $request): RedirectResponse|JsonResponse
    {
        CalendarEntry::create([
            'entry_date' => $request->date('entry_date')->toDateString(),
            'title' => $request->string('title')->toString(),
            'details' => $request->filled('details') ? $request->string('details')->toString() : null,
            'follow_up_enabled' => $request->boolean('follow_up_enabled'),
            'follow_up_days' => $request->boolean('follow_up_enabled') ? $request->integer('follow_up_days') : null,
            'source_type' => 'self',
            'source_id' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('dashboard', ['month' => $request->date('entry_date')->format('Y-m'), 'date' => $request->date('entry_date')->toDateString()])]);
        }

        return redirect()
            ->route('dashboard', array_filter([
                'month' => $request->string('month')->toString(),
            ]))
            ->with('status', 'calendar-entry-created');
    }

    public function update(Request $request, CalendarEntry $calendarEntry): JsonResponse
    {
        abort_unless($calendarEntry->source_type === 'self' && $calendarEntry->source_id === $request->user()->id, 403);
        $data = $request->validate((new StoreCalendarEntryRequest)->rules());
        unset($data['month']);
        if (! $data['follow_up_enabled']) {
            $data['follow_up_days'] = null;
        }
        $calendarEntry->update($data);

        return response()->json(['redirect' => route('dashboard', ['month' => $calendarEntry->entry_date->format('Y-m'), 'date' => $calendarEntry->entry_date->toDateString()])]);
    }

    public function destroy(Request $request, CalendarEntry $calendarEntry): JsonResponse
    {
        abort_unless($calendarEntry->source_type === 'self' && $calendarEntry->source_id === $request->user()->id, 403);
        $request->validate(['confirmed' => ['required', 'accepted']]);
        $date = $calendarEntry->entry_date;
        $calendarEntry->delete();

        return response()->json(['redirect' => route('dashboard', ['month' => $date->format('Y-m'), 'date' => $date->toDateString()])]);
    }

    private function buildReminderDays(Collection $reminderDisplayDates, Collection $reminderEntries): array
    {
        $days = $reminderDisplayDates
            ->values()
            ->map(function (CarbonImmutable $date, int $offset) use ($reminderEntries): array {
                $modalLabel = $date->isoFormat('dddd, D MMMM YYYY');

                return [
                    'date' => $date,
                    'date_display' => $date->isoFormat('ddd, D MMM'),
                    'label' => match ($offset) {
                        0 => __('Today'),
                        1 => __('Tomorrow'),
                        default => $date->isoFormat('dddd'),
                    },
                    'label_kind' => in_array($offset, [0, 1], true) ? 'priority' : 'default',
                    'modal_label' => $modalLabel,
                    'entries' => $reminderEntries->get($date->toDateString(), collect()),
                    'empty_message' => __('Nothing lined up here yet.'),
                ];
            })
            ->values();

        return $this->collapseWeekendReminderDays($days->all(), $reminderEntries);
    }

    private function collapseWeekendReminderDays(array $days, Collection $reminderEntries): array
    {
        $collapsed = [];
        $handledWeekendStarts = [];

        foreach ($days as $current) {
            if (in_array($current['date']->dayOfWeekIso, [6, 7], true)) {
                $weekendStart = $current['date']->dayOfWeekIso === 6
                    ? $current['date']
                    : $current['date']->subDay();
                $weekendKey = $weekendStart->toDateString();

                if (in_array($weekendKey, $handledWeekendStarts, true)) {
                    continue;
                }

                $weekendEnd = $weekendStart->addDay();
                $weekendEntries = $reminderEntries->get($weekendStart->toDateString(), collect())
                    ->concat($reminderEntries->get($weekendEnd->toDateString(), collect()))
                    ->sortBy(fn (array $entry) => $entry['date']->toDateString().'|'.($entry['id'] ?? ''));
                $collapsed[] = [
                    'date' => $weekendStart,
                    'date_display' => $weekendStart->isoFormat('ddd, D MMM').' & '.$weekendEnd->isoFormat('ddd, D MMM'),
                    'label' => __('Weekend'),
                    'label_kind' => 'default',
                    'modal_label' => __('Weekend').': '.$weekendStart->isoFormat('dddd, D MMMM').' - '.$weekendEnd->isoFormat('dddd, D MMMM YYYY'),
                    'entries' => $weekendEntries->values(),
                    'empty_message' => __('Nothing lined up here.'),
                ];
                $handledWeekendStarts[] = $weekendKey;

                continue;
            }

            $collapsed[] = $current;
        }

        return $collapsed;
    }
}
