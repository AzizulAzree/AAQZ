<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Private Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('A simple monthly calendar with manual entry summaries.') }}</p>
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
                    <p>{{ __('You are signed in to the private area of this app.') }}</p>
                    <p class="text-sm text-gray-600">{{ __('Public self-registration is disabled. Create or manage your single local account with Artisan when needed.') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                            <p class="text-sm text-gray-500">{{ __('Monthly overview') }}</p>
                        </div>

                        <a
                            href="{{ route('dashboard', ['month' => $calendar->nextMonthQuery()]) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            {{ __('Next') }}
                        </a>
                    </div>

                    <div class="mt-6 grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-gray-200 bg-gray-200">
                        @foreach ($calendar->weekdayLabels as $weekday)
                            <div class="bg-gray-50 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ $weekday }}
                            </div>
                        @endforeach

                        @foreach ($calendar->weeks as $week)
                            @foreach ($week as $day)
                                <div
                                    data-date="{{ $day['date']->toDateString() }}"
                                    class="{{ $day['is_current_month'] ? 'bg-white' : 'bg-gray-50' }} min-h-32 p-3 align-top"
                                >
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

                                    <div class="mt-3 space-y-2">
                                        @foreach ($day['entries']->take(3) as $entry)
                                            <div class="rounded-md border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-700">
                                                <div class="font-medium text-gray-900">{{ $entry['title'] }}</div>
                                                @if ($entry['details'])
                                                    <div class="mt-1 text-gray-500">{{ $entry['details'] }}</div>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if ($day['entries']->count() > 3)
                                            <div class="text-xs text-gray-500">
                                                {{ __('+:count more', ['count' => $day['entries']->count() - 3]) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        {{ __('Entries are grouped by day from the calendar_entries table today, with room to merge in other sources later.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
