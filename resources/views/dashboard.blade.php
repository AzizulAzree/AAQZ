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
                <div class="p-4 text-gray-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-500">
                                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                                {{ __('Reminder') }}
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-gray-900">{{ __('The next few days at a glance') }}</h3>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Stay on top of what needs your attention today and over the next two days.') }}</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="rounded-full border border-gray-200 px-2.5 py-1">
                                {{ trans_choice('{0} No plans yet|{1} :count reminder|[2,*] :count reminders', collect($reminderDays)->sum(fn ($day) => $day['entries']->count()), ['count' => collect($reminderDays)->sum(fn ($day) => $day['entries']->count())]) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 lg:grid-cols-3">
                        @foreach ($reminderDays as $reminderDay)
                            <section class="rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 px-3.5 py-3.5 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">{{ $reminderDay['label'] }}</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $reminderDay['date']->isoFormat('D MMM') }}</p>
                                    </div>
                                    <span class="rounded-full bg-white/90 px-2 py-1 text-[11px] font-medium text-gray-500 ring-1 ring-gray-200">
                                        {{ trans_choice('{0} Open|{1} :count item|[2,*] :count items', $reminderDay['entries']->count(), ['count' => $reminderDay['entries']->count()]) }}
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2">
                                    @forelse ($reminderDay['entries']->take(3) as $entry)
                                        <button
                                            type="button"
                                            x-data="{ hovered: false }"
                                            x-on:mouseenter="hovered = true"
                                            x-on:mouseleave="hovered = false"
                                            x-on:click="showEntryModal(@js([
                                                'id' => $entry['id'],
                                                'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                'title' => $entry['title'],
                                                'details' => $entry['details'],
                                                'source_type' => $entry['source_type'],
                                                'source_id' => $entry['source_id'],
                                                'owner_name' => $entry['owner_name'],
                                                'owner_color' => $entry['owner_color'],
                                                'created_at' => $entry['created_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                'updated_at' => $entry['updated_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                            ]))"
                                            class="flex w-full items-center gap-2 truncate rounded-full border border-gray-200 bg-white px-2.5 py-1.5 text-left text-xs font-medium text-gray-700 transition duration-150 hover:shadow-sm"
                                            title="{{ $entry['details'] ?: __('Open this entry to see more information.') }}"
                                            @if ($entry['owner_color'])
                                                x-bind:style="'background-color: ' + (hovered ? rgba('{{ $entry['owner_color'] }}', 0.12) : '#ffffff')"
                                            @endif
                                        >
                                            @if ($entry['owner_color'])
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $entry['owner_color'] }}"></span>
                                            @endif
                                            <span class="truncate">{{ $entry['title'] }}</span>
                                        </button>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-gray-200 bg-white/80 px-3 py-3 text-xs text-gray-400">
                                            {{ __('Nothing lined up here yet.') }}
                                        </div>
                                    @endforelse

                                    @if ($reminderDay['entries']->count() > 3)
                                        <button
                                            type="button"
                                            x-on:click="showDayModal(
                                                @js($reminderDay['date']->isoFormat('dddd, D MMMM YYYY')),
                                                @js(
                                                    $reminderDay['entries']->map(fn ($entry) => [
                                                        'id' => $entry['id'],
                                                        'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                        'title' => $entry['title'],
                                                        'details' => $entry['details'],
                                                        'source_type' => $entry['source_type'],
                                                        'source_id' => $entry['source_id'],
                                                        'owner_name' => $entry['owner_name'],
                                                        'owner_color' => $entry['owner_color'],
                                                        'created_at' => $entry['created_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                        'updated_at' => $entry['updated_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                    ])->values()
                                                )
                                            )"
                                            class="text-xs font-medium text-rose-500 hover:text-rose-600"
                                        >
                                            {{ __('+:count more', ['count' => $reminderDay['entries']->count() - 3]) }}
                                        </button>
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
            </div>

            <div
                x-data="{
                    selectedEntry: null,
                    selectedDay: null,
                    selectedDayEntries: [],
                    createEntryDate: @js(old('entry_date', $createEntryDate)),
                    createEntryLabel: @js(old('entry_date_label', $createEntryLabel)),
                    rgba(color, alpha) {
                        if (! color || ! color.startsWith('#')) {
                            return '';
                        }

                        const normalized = color.length === 4
                            ? `#${color[1]}${color[1]}${color[2]}${color[2]}${color[3]}${color[3]}`
                            : color;

                        const hex = normalized.slice(1);

                        if (hex.length !== 6) {
                            return '';
                        }

                        const r = Number.parseInt(hex.slice(0, 2), 16);
                        const g = Number.parseInt(hex.slice(2, 4), 16);
                        const b = Number.parseInt(hex.slice(4, 6), 16);

                        if ([r, g, b].some(Number.isNaN)) {
                            return '';
                        }

                        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                    },
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
                    showCreateModal(date, label) {
                        this.createEntryDate = date;
                        this.createEntryLabel = label;
                        $dispatch('open-modal', 'calendar-entry-create');
                    },
                }"
                x-init="
                    @if ($errors->hasBag('default') && old('entry_date'))
                        $dispatch('open-modal', 'calendar-entry-create');
                    @endif
                "
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
            >
                <div class="p-6">
                    @if (session('status') === 'calendar-entry-created')
                        <p class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ __('Entry added to your calendar.') }}
                        </p>
                    @endif

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
                                                            <button
                                                                type="button"
                                                                x-on:click="showCreateModal(
                                                                    @js($day['date']->toDateString()),
                                                                    @js($day['date']->isoFormat('dddd, D MMMM YYYY'))
                                                                )"
                                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition hover:bg-gray-100 {{ $day['is_today'] ? 'bg-gray-900 text-white hover:bg-gray-800' : ($day['is_current_month'] ? 'text-gray-900' : 'text-gray-400') }}"
                                                                title="{{ __('Add something for this date') }}"
                                                            >
                                                                {{ $day['date']->day }}
                                                            </button>
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
                                                                    x-data="{ hovered: false }"
                                                                    x-on:mouseenter="hovered = true"
                                                                    x-on:mouseleave="hovered = false"
                                                                    x-on:click="showEntryModal(@js([
                                                                        'id' => $entry['id'],
                                                                        'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                                        'title' => $entry['title'],
                                                                        'details' => $entry['details'],
                                                                        'source_type' => $entry['source_type'],
                                                                        'source_id' => $entry['source_id'],
                                                                        'owner_name' => $entry['owner_name'],
                                                                        'owner_color' => $entry['owner_color'],
                                                                        'created_at' => $entry['created_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                                        'updated_at' => $entry['updated_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                                    ]))"
                                                                    class="flex w-full items-center gap-2 truncate rounded-full border border-gray-200 bg-white px-2.5 py-1.5 text-left text-xs font-medium text-gray-700 transition duration-150 hover:shadow-sm"
                                                                    title="{{ $entry['details'] ?: __('Open this entry to see more information.') }}"
                                                                    @if ($entry['owner_color'])
                                                                        x-bind:style="'background-color: ' + (hovered ? rgba('{{ $entry['owner_color'] }}', 0.12) : '#ffffff')"
                                                                    @endif
                                                                >
                                                                    @if ($entry['owner_color'])
                                                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $entry['owner_color'] }}"></span>
                                                                    @endif
                                                                    <span class="truncate">{{ $entry['title'] }}</span>
                                                                </button>
                                                            @endforeach

                                                            @if ($day['entries']->count() > 3)
                                                                <button
                                                                    type="button"
                                                                    x-on:click="showDayModal(
                                                                        @js($day['date']->isoFormat('dddd, D MMMM YYYY')),
                                                                        @js(
                                                                            $day['entries']->map(fn ($entry) => [
                                                                                'id' => $entry['id'],
                                                                                'date' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
                                                                                'title' => $entry['title'],
                                                                                'details' => $entry['details'],
                                                                                'source_type' => $entry['source_type'],
                                                                                'source_id' => $entry['source_id'],
                                                                                'owner_name' => $entry['owner_name'],
                                                                                'owner_color' => $entry['owner_color'],
                                                                                'created_at' => $entry['created_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
                                                                                'updated_at' => $entry['updated_at']?->isoFormat('ddd, D MMM YYYY, h:mm A'),
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
                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400">
                                    <div class="flex items-center gap-1.5" x-show="selectedEntry?.owner_color" :title="selectedEntry?.owner_name || '{{ __('Owner') }}'">
                                        <span
                                            class="h-2.5 w-2.5 rounded-full"
                                            :style="selectedEntry?.owner_color ? `background-color: ${selectedEntry.owner_color}` : ''"
                                        ></span>
                                        <span class="sr-only" x-text="selectedEntry?.owner_name || '{{ __('Owner') }}'"></span>
                                    </div>
                                    <span x-show="selectedEntry?.created_at">
                                        {{ __('Created') }}
                                        <span x-text="selectedEntry?.created_at"></span>
                                    </span>
                                    <span x-show="selectedEntry?.updated_at && selectedEntry?.updated_at !== selectedEntry?.created_at">
                                        {{ __('Updated') }}
                                        <span x-text="selectedEntry?.updated_at"></span>
                                    </span>
                                </div>
                            </div>
                            <button
                                type="button"
                                x-on:click="$dispatch('close-modal', 'calendar-entry-details')"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>

                        <div class="mt-5 space-y-4 text-sm text-gray-700">
                            <div class="rounded-2xl bg-gray-50 px-4 py-4">
                                <p class="leading-6 text-gray-600" x-text="selectedEntry?.details || '{{ __('No additional details for this entry.') }}'"></p>
                            </div>

                            <template x-if="selectedEntry?.source_type && selectedEntry.source_type !== 'self'">
                                <div class="border-t border-gray-100 pt-4 text-xs text-gray-400">
                                    <p>
                                        <span x-text="selectedEntry?.source_type"></span>
                                        <span x-show="selectedEntry?.source_id">#<span x-text="selectedEntry?.source_id"></span></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-modal>

                <x-modal name="calendar-entry-create" maxWidth="lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Add to') }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900" x-text="createEntryLabel || '{{ __('Selected date') }}'"></h3>
                            </div>
                            <button
                                type="button"
                                x-on:click="$dispatch('close-modal', 'calendar-entry-create')"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>

                        <form method="POST" action="{{ route('dashboard.entries.store') }}" class="mt-6 space-y-4">
                            @csrf

                            <input type="hidden" name="entry_date" :value="createEntryDate">
                            <input type="hidden" name="entry_date_label" :value="createEntryLabel">
                            <input type="hidden" name="month" value="{{ $selectedMonthQuery }}">

                            <div>
                                <x-input-label for="calendar-entry-title" :value="__('Title')" />
                                <x-text-input
                                    id="calendar-entry-title"
                                    name="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('title')"
                                    required
                                    maxlength="255"
                                    autocomplete="off"
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="calendar-entry-details-field" :value="__('Details')" />
                                <textarea
                                    id="calendar-entry-details-field"
                                    name="details"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                                >{{ old('details') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('details')" />
                            </div>

                            <x-input-error class="mt-2" :messages="$errors->get('entry_date')" />

                            <div class="flex items-center gap-3">
                                <x-primary-button>{{ __('Save entry') }}</x-primary-button>
                                <p class="text-xs text-gray-500">{{ __('This will be linked to your account automatically.') }}</p>
                            </div>
                        </form>
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
                            <template x-for="entry in selectedDayEntries" :key="entry.id">
                                <button
                                    type="button"
                                    x-data="{ hovered: false }"
                                    x-on:mouseenter="hovered = true"
                                    x-on:mouseleave="hovered = false"
                                    x-on:click="selectedEntry = entry; $dispatch('close-modal', 'calendar-day-details'); $dispatch('open-modal', 'calendar-entry-details')"
                                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-left hover:border-gray-300 hover:bg-gray-100"
                                    :style="entry.owner_color ? `background-color: ${hovered ? rgba(entry.owner_color, 0.12) : '#f9fafb'}` : ''"
                                    :title="entry.details || '{{ __('Open this entry to see more information.') }}'"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                                            :style="entry.owner_color ? `background-color: ${entry.owner_color}` : ''"
                                            x-show="entry.owner_color"
                                        ></span>
                                        <div class="truncate text-sm font-medium text-gray-900" x-text="entry.title"></div>
                                    </div>
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
