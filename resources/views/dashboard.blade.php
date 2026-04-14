<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Calendar') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('See upcoming plans and recent activity at a glance.') }}</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ __('Today: :date', ['date' => $calendar->today->isoFormat('ddd, D MMM YYYY')]) }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-2">
                    <p>{{ __('Welcome back.') }}</p>
                    <p class="text-sm text-gray-600">{{ __('Your calendar and account tools are ready whenever you need them.') }}</p>
                </div>
            </div>

            <div
                x-data="{
                    selectedEntry: null,
                    selectedDay: null,
                    selectedDayEntries: [],
                    showEntryModal(entry) {
                        this.selectedDay = null;
                        this.selectedDayEntries = [];
                        this.selectedEntry = entry;
                        $dispatch('open-modal', 'calendar-entry-details');
                    },
                    showDayModal(dayLabel, entries) {
                        this.selectedEntry = null;
                        this.selectedDay = dayLabel;
                        this.selectedDayEntries = entries;
                        $dispatch('open-modal', 'calendar-day-details');
                    },
                }"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between gap-4">
                        <a
                            href="{{ route('dashboard', ['month' => $calendar->previousMonthQuery()]) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Previous') }}
                        </a>

                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $calendar->heading() }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Month view') }}</p>
                        </div>

                        <a
                            href="{{ route('dashboard', ['month' => $calendar->nextMonthQuery()]) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Next') }}
                        </a>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-gray-200">
                            <table data-calendar-grid class="min-w-full table-fixed border-separate border-spacing-px bg-gray-200">
                                <thead>
                                    <tr>
                                        @foreach ($calendar->weekdayLabels as $weekday)
                                            <th scope="col" class="bg-gray-50 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ $weekday }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($calendar->weeks as $weekIndex => $week)
                                        <tr data-calendar-week="{{ $weekIndex + 1 }}" class="align-top">
                                            @foreach ($week as $day)
                                                <td
                                                    data-date="{{ $day['date']->toDateString() }}"
                                                    class="{{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50' }} w-1/7"
                                                >
                                                    <div class="h-40 p-3">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span
                                                                class="{{ $day['is_today'] ? 'bg-gray-900 text-white' : ($day['is_current_month'] ? 'text-gray-900' : 'text-gray-400') }} inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                                                            >
                                                                {{ $day['date']->day }}
                                                            </span>
                                                            @if ($day['entries']->isNotEmpty())
                                                                <span class="text-xs text-gray-400">
                                                                    {{ trans_choice('{1} :count item|[2,*] :count items', $day['entries']->count(), ['count' => $day['entries']->count()]) }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="mt-3 space-y-1.5">
                                                            @foreach ($day['entries']->take(3) as $entry)
                                                                <button
                                                                    type="button"
                                                                    x-on:click="showEntryModal(@js([
                                                                        'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                                        'title' => $entry['title'],
                                                                        'details' => $entry['details'],
                                                                        'source_type' => $entry['source_type'],
                                                                        'source_id' => $entry['source_id'],
                                                                    ]))"
                                                                    class="block w-full truncate rounded-md border border-gray-200 bg-gray-50 px-2 py-1.5 text-left text-xs font-medium text-gray-700 hover:border-gray-300 hover:bg-gray-100"
                                                                    title="{{ $entry['title'] }}"
                                                                >
                                                                    {{ $entry['title'] }}
                                                                </button>
                                                            @endforeach

                                                            @if ($day['entries']->count() > 3)
                                                                <button
                                                                    type="button"
                                                                    x-on:click="showDayModal(
                                                                        @js($day['date']->isoFormat('dddd, D MMMM YYYY')),
                                                                        @js(
                                                                            $day['entries']->map(fn ($entry) => [
                                                                                'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                                                'title' => $entry['title'],
                                                                                'details' => $entry['details'],
                                                                                'source_type' => $entry['source_type'],
                                                                                'source_id' => $entry['source_id'],
                                                                            ])->values()
                                                                        )
                                                                    )"
                                                                    class="text-xs font-medium text-gray-500 hover:text-gray-700"
                                                                >
                                                                    {{ __('+:count more', ['count' => $day['entries']->count() - 3]) }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        {{ __('Select any item to view more details.') }}
                    </p>
                </div>

                <x-modal name="calendar-entry-details" maxWidth="lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500" x-text="selectedEntry?.date"></p>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900" x-text="selectedEntry?.title"></h3>
                            </div>
                            <button
                                type="button"
                                x-on:click="$dispatch('close-modal', 'calendar-entry-details')"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>

                        <div class="mt-6 space-y-4 text-sm text-gray-700">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('Details') }}</p>
                                <p class="mt-1 text-gray-600" x-text="selectedEntry?.details || '{{ __('No additional details for this entry.') }}'"></p>
                            </div>

                            <template x-if="selectedEntry?.source_type || selectedEntry?.source_id">
                                <div>
                                    <p class="font-medium text-gray-900">{{ __('Reference') }}</p>
                                    <p class="mt-1 text-gray-600">
                                        <span x-text="selectedEntry?.source_type || '{{ __('Added here') }}'"></span>
                                        <span x-show="selectedEntry?.source_id">#<span x-text="selectedEntry?.source_id"></span></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-modal>

                <x-modal name="calendar-day-details" maxWidth="2xl">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('More for') }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900" x-text="selectedDay"></h3>
                            </div>
                            <button
                                type="button"
                                x-on:click="$dispatch('close-modal', 'calendar-day-details')"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>

                        <div class="mt-6 space-y-3">
                            <template x-for="entry in selectedDayEntries" :key="`${entry.date}-${entry.title}`">
                                <button
                                    type="button"
                                    x-on:click="selectedEntry = entry; $dispatch('close-modal', 'calendar-day-details'); $dispatch('open-modal', 'calendar-entry-details')"
                                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-left hover:border-gray-300 hover:bg-gray-100"
                                >
                                    <div class="truncate text-sm font-medium text-gray-900" x-text="entry.title"></div>
                                    <div class="mt-1 truncate text-xs text-gray-500" x-text="entry.details || '{{ __('No additional details for this entry.') }}'"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
