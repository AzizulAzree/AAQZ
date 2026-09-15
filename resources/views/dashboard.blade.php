<x-app-layout>
    @php
        $entryData = fn ($entry) => [
            'id' => $entry['id'], 'title' => $entry['title'], 'details' => $entry['details'],
            'date' => $entry['date']->toDateString(), 'date_label' => $entry['date']->isoFormat('ddd, D MMM YYYY'),
            'original_date' => $entry['model']->entry_date->toDateString(),
            'is_follow_up' => $entry['is_follow_up'], 'follow_up_enabled' => $entry['follow_up_enabled'], 'follow_up_days' => $entry['follow_up_days'],
            'owner_name' => $entry['owner_name'] ?? 'Unassigned', 'owner_color' => $entry['owner_color'] ?? '#718578',
            'can_manage' => $entry['source_type'] === 'self' && $entry['source_id'] === auth()->id(),
            'manage_url' => route('dashboard.entries.update', $entry['model']),
        ];
        $days = collect($calendar->weeks)->flatten(1)->map(fn ($day) => [
            'date' => $day['date']->toDateString(), 'label' => $day['date']->isoFormat('dddd, D MMMM'),
            'number' => $day['date']->day, 'weekday' => $day['date']->isoFormat('ddd'),
            'today' => $day['is_today'], 'current' => $day['is_current_month'],
            'entries' => $day['is_current_month'] ? $day['entries']->map($entryData)->values()->all() : [],
        ])->values();
        $config = [
            'days' => $days, 'today' => $calendar->today->toDateString(),
            'initialDate' => $calendar->today->isSameMonth($calendar->month) ? $calendar->today->toDateString() : $calendar->month->toDateString(),
            'baseUrl' => route('dashboard'), 'storeUrl' => route('dashboard.entries.store'),
            'holidayUrl' => route('dashboard.holidays', ['year' => $calendar->month->year]), 'holidayStates' => config('calendar.states'),
            'upcomingHolidayUrl' => $calendar->month->year !== $calendar->today->year ? route('dashboard.holidays', ['year' => $calendar->today->year]) : null,
        ];
    @endphp
    <div class="cal-page" x-data="calendarDashboard(@js($config))" x-on:keydown.escape.window="close()">
        <header class="cal-header">
            <div><p class="cal-eyebrow">MAKE ROOM FOR WHAT MATTERS</p><h1>Calendar</h1></div>
            <button type="button" class="pr-button pr-button-dark" x-on:click="open('create')"><x-project-icon name="plus"/> Add entry</button>
        </header>
        @if (session('status') === 'calendar-entry-created')<p class="pr-notice" role="status">Entry added to your calendar.</p>@endif
        <div class="cal-layout">
            <section class="cal-month-panel" :class="{ 'cal-show-month': view === 'month' }" aria-label="Calendar month">
                <div class="cal-month-toolbar">
                    <label class="cal-month-picker"><span class="sr-only">Choose month</span><input type="month" value="{{ $selectedMonthQuery }}" x-on:change="changeMonth($event.target.value)" aria-label="Choose month"></label>
                    <div class="cal-month-actions"><a class="pr-button" href="{{ route('dashboard') }}">Today</a><a class="cal-arrow" aria-label="Previous month" href="{{ route('dashboard', ['month' => $calendar->previousMonthQuery()]) }}"><x-project-icon name="chevron" class="cal-arrow-back"/></a><a class="cal-arrow" aria-label="Next month" href="{{ route('dashboard', ['month' => $calendar->nextMonthQuery()]) }}"><x-project-icon name="chevron"/></a></div>
                </div>
                <div class="cal-holiday-controls"><label for="cal-holiday-region">Holidays</label><select id="cal-holiday-region" x-model="holidayRegion" x-on:change="changeHolidayRegion()"><option value="national">Nationwide</option>@foreach(config('calendar.states') as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach</select><span class="cal-holiday-status" x-show="holidayStatus === 'unavailable'">Dates unavailable</span><span class="cal-holiday-status" x-show="holidayStatus === 'empty'">No published dates for this year</span><span class="cal-holiday-status" x-show="holidayStatus === 'stale'">Showing saved dates</span></div>
                <h2 class="sr-only">{{ $calendar->heading() }}</h2>
                <div class="cal-mobile-tabs" aria-label="Calendar view"><button type="button" :aria-pressed="view === 'agenda'" x-on:click="view = 'agenda'">Agenda</button><button type="button" :aria-pressed="view === 'month'" x-on:click="view = 'month'">Month</button></div>
                <div class="cal-week-strip" aria-label="Select a day">
                    <template x-for="day in week" :key="day.date"><button type="button" :class="{ 'is-selected': selectedDate === day.date, 'is-today': day.today }" :aria-label="day.label" :aria-pressed="selectedDate === day.date" x-on:click="day.current ? selectDay(day.date) : changeMonth(day.date.slice(0,7))"><span x-text="day.weekday"></span><strong x-text="day.number"></strong><i :class="{ 'has-entries': day.entries.length, 'has-holiday': holidaysFor(day.date).length }"></i></button></template>
                </div>
                <div class="cal-grid" data-calendar-grid role="group" aria-label="{{ $calendar->heading() }}">
                    <div class="cal-weekdays">@foreach ($calendar->weekdayLabels as $label)<span>{{ $label }}</span>@endforeach</div>
                    @foreach ($calendar->weeks as $weekIndex => $week)
                    <div class="cal-week" data-calendar-week="{{ $weekIndex + 1 }}">
                        @foreach ($week as $day)
                        @php($date = $day['date']->toDateString())
                        <button type="button" class="cal-day {{ $day['is_current_month'] ? '' : 'cal-day-outside' }} {{ $day['is_today'] ? 'cal-day-today' : '' }}" data-date="{{ $date }}" :class="{ 'cal-day-selected': selectedDate === '{{ $date }}' }" :aria-pressed="selectedDate === '{{ $date }}'" aria-label="{{ $day['date']->isoFormat('dddd, D MMMM YYYY') }}{{ $day['is_current_month'] ? ', '.$day['entries']->count().' entries' : '' }}" x-on:click="{{ $day['is_current_month'] ? "selectDay('$date')" : "changeMonth('".$day['date']->format('Y-m')."')" }}">
                            <span class="cal-day-number">{{ $day['date']->day }}</span>
                            @if ($day['is_current_month'])
                            <span class="cal-day-events">
                                @foreach ($day['entries']->take(2) as $entry)
                                <span class="cal-event-preview"><i style="background-color: {{ $entry['owner_color'] ?? '#718578' }}"></i><span>{{ $entry['title'] }}</span>@if($entry['is_follow_up'])<span class="cal-follow-indicator" aria-label="Follow Up">↻</span>@endif</span>
                                @endforeach
                                @if ($day['entries']->count() > 2)<span class="cal-more">+{{ $day['entries']->count() - 2 }} more</span>@endif
                            </span>
                            @if($day['entries']->isNotEmpty())<span class="cal-mobile-dot" aria-hidden="true"></span>@endif
                            <span class="cal-holiday-ribbon" x-show="holidaysFor('{{ $date }}').some(item => item.kind === 'holiday')" x-cloak><span class="cal-holiday-ribbon-band" aria-hidden="true">PH</span><span class="sr-only" x-text="holidaysFor('{{ $date }}').filter(item => item.kind === 'holiday').map(item => 'Public holiday: ' + item.name).join(', ')"></span></span>
                            <span class="cal-holiday-ribbon cal-observance-ribbon" :class="{ 'cal-ribbon-secondary': holidaysFor('{{ $date }}').some(item => item.kind === 'holiday') }" x-show="holidaysFor('{{ $date }}').some(item => item.kind === 'observance')" x-cloak><span class="cal-holiday-ribbon-band" aria-hidden="true">OBS</span><span class="sr-only" x-text="holidaysFor('{{ $date }}').filter(item => item.kind === 'observance').map(item => 'Observance: ' + item.name).join(', ')"></span></span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </section>
            <aside class="cal-agenda" aria-label="Daily agenda">
                <section class="cal-agenda-day">
                    <div class="cal-agenda-heading"><div><p class="cal-eyebrow" x-text="selectedDate === today ? 'TODAY' : 'YOUR DAY'"></p><h2 x-text="day.label"></h2></div><span class="cal-count" x-text="day.entries.length"></span></div>
                    <div class="cal-agenda-list">
                        <template x-for="entry in day.entries" :key="entry.id">
                            <button type="button" class="cal-agenda-entry" x-on:click="open('detail', entry)"><span class="cal-owner-dot" :style="{backgroundColor:entry.owner_color}"></span><span class="cal-entry-copy"><strong x-text="entry.title"></strong><span class="cal-entry-description" x-show="entry.details" x-text="entry.details"></span><span class="cal-entry-meta"><span x-text="entry.owner_name"></span><span class="cal-badge" x-show="entry.is_follow_up">Follow Up</span></span></span><x-project-icon name="chevron"/></button>
                        </template>
                        <div class="cal-day-empty" x-show="!day.entries.length"><x-project-icon name="clock"/><h3>A little breathing room</h3><p>No entries for this day.</p></div>
                    </div>
                    <div class="cal-day-notes" x-show="holidaysFor(selectedDate).length" x-cloak>
                        <template x-for="holiday in holidaysFor(selectedDate)" :key="holiday.kind + holiday.name"><div class="cal-day-note"><span class="cal-note-kind" x-text="holiday.kind === 'holiday' ? 'Public holiday' : 'Observance'"></span><span x-text="holiday.name"></span><small x-show="holiday.tentative">Date subject to confirmation</small></div></template>
                    </div>
                    <button class="cal-add-row" type="button" x-on:click="open('create')"><x-project-icon name="plus"/> Add entry</button>
                </section>
                <section class="cal-upcoming">
                    <div class="cal-section-heading"><x-project-icon name="clock"/><h2>Upcoming</h2></div>
                    @foreach ($reminderDays as $reminderDay)
                        <div class="cal-upcoming-day"><div class="cal-upcoming-label"><strong>{{ $reminderDay['label'] }}</strong><span>{{ $reminderDay['date_display'] }}</span></div>
                        @forelse ($reminderDay['entries'] as $entry)
                            <button type="button" class="cal-upcoming-entry" x-on:click="open('detail', @js($entryData($entry)))"><span class="cal-owner-dot" style="background-color: {{ $entry['owner_color'] ?? '#718578' }}"></span><span><strong>{{ $entry['title'] }}</strong><small>{{ $entry['owner_name'] ?? 'Unassigned' }}@if($entry['is_follow_up']) · Follow Up @endif</small></span><x-project-icon name="chevron"/></button>
                        @empty
                            <p class="cal-upcoming-empty">No entries</p>
                        @endforelse
                        <span class="cal-upcoming-holiday" x-show="holidaysFor('{{ $reminderDay['date']->toDateString() }}').length" x-text="holidayLabel('{{ $reminderDay['date']->toDateString() }}')" x-cloak></span></div>
                    @endforeach
                </section>
            </aside>
        </div>
        <template x-if="modal">
            <div class="cal-modal-overlay" x-on:click.self="close()">
                <section class="cal-dialog" :role="modal === 'delete' ? 'alertdialog' : 'dialog'" aria-modal="true" aria-labelledby="cal-dialog-title" :aria-busy="busy" x-on:keydown="trap($event)">
                    <div class="cal-dialog-heading"><div><span class="cal-dialog-kicker" x-show="modal === 'detail'" x-text="selectedEntry?.date_label"></span><h2 id="cal-dialog-title" x-text="modal === 'create' ? 'Add entry' : modal === 'edit' ? 'Edit entry' : modal === 'delete' ? 'Delete entry?' : selectedEntry?.title"></h2></div><button id="cal-modal-cancel" type="button" class="pr-button" x-on:click="close()" :disabled="busy" x-text="modal === 'delete' ? 'Cancel' : 'Close'"></button></div>
                    <template x-if="modal === 'detail'"><div>
                        <div class="cal-detail-meta"><span class="cal-owner-dot" :style="{backgroundColor:selectedEntry.owner_color}"></span><span x-text="selectedEntry.owner_name"></span><span class="cal-badge" x-show="selectedEntry.is_follow_up">Follow Up</span></div>
                        <p class="cal-detail-description" x-text="selectedEntry.details || 'No additional details.'"></p>
                        <p class="cal-follow-summary" x-show="selectedEntry.follow_up_enabled && !selectedEntry.is_follow_up" x-text="`Follow-up after ${selectedEntry.follow_up_days} days`"></p>
                        <p class="cal-follow-summary" x-show="selectedEntry.is_follow_up">Changes apply to the original entry and its follow-up.</p>
                        <div class="cal-dialog-footer" x-show="selectedEntry.can_manage"><button type="button" class="pr-button pr-danger-text" x-on:click="open('delete', selectedEntry)"><x-project-icon name="trash"/> Delete</button><button type="button" class="pr-button pr-button-dark" x-on:click="open('edit', selectedEntry)"><x-project-icon name="edit"/> Edit entry</button></div>
                    </div></template>
                    <template x-if="modal === 'create' || modal === 'edit'">
                        <form class="cal-form" x-on:submit.prevent="submit()">
                            <label for="cal-title-input">Title</label><input id="cal-title-input" x-model="form.title" required maxlength="255" :disabled="busy" autocomplete="off">
                            <label for="cal-date-input">Date</label><input id="cal-date-input" type="date" x-model="form.entry_date" required :disabled="busy">
                            <label for="cal-details-input">Details <span>(optional)</span></label><textarea id="cal-details-input" x-model="form.details" rows="3" :disabled="busy"></textarea>
                            <div class="cal-follow-box"><label class="cal-follow-toggle"><input type="checkbox" x-model="form.follow_up_enabled" :disabled="busy"><span>Add a follow-up</span></label><div x-show="form.follow_up_enabled" class="cal-follow-fields"><label for="cal-follow-days">After</label><input id="cal-follow-days" type="number" min="1" max="30" x-model.number="form.follow_up_days" :required="form.follow_up_enabled" :disabled="!form.follow_up_enabled || busy"><span>days</span><small x-text="followUpLabel"></small></div></div>
                            <p class="cal-error" role="alert" x-show="error" x-text="error"></p>
                            <div class="cal-dialog-footer"><button type="button" class="pr-button" x-on:click="close()" :disabled="busy">Cancel</button><button type="submit" class="pr-button pr-button-dark" :disabled="busy" x-text="busy ? 'Saving…' : modal === 'edit' ? 'Save changes' : 'Save entry'"></button></div>
                        </form>
                    </template>
                    <template x-if="modal === 'delete'"><form x-on:submit.prevent="submit()"><div class="cal-delete-warning"><x-project-icon name="warning"/><strong x-text="selectedEntry.title"></strong><p>This permanently deletes the entry and its follow-up reminder, if enabled.</p><p>This cannot be undone.</p></div><p class="cal-error" role="alert" x-show="error" x-text="error"></p><div class="cal-dialog-footer"><button type="button" class="pr-button" x-on:click="close()" :disabled="busy">Cancel</button><button type="submit" class="pr-button pr-button-danger" :disabled="busy" x-text="busy ? 'Deleting…' : 'Delete entry'"></button></div></form></template>
                </section>
            </div>
        </template>
    </div>
</x-app-layout>
