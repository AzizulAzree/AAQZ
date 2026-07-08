<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Finance') }}
            </h2>

            @if ($canManageAccess)
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 transition hover:bg-gray-50 hover:text-gray-800"
                    onclick="window.dispatchEvent(new CustomEvent('open-finance-access-modal'))"
                    title="{{ __('Access Settings') }}"
                    aria-label="{{ __('Access Settings') }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 018.82 1h2.36a1 1 0 01.98.804l.331 1.652a6.973 6.973 0 011.43.824l1.556-.52a1 1 0 011.144.447l1.18 2.043a1 1 0 01-.163 1.216l-1.225 1.132a7.11 7.11 0 010 1.648l1.225 1.132a1 1 0 01.163 1.216l-1.18 2.043a1 1 0 01-1.144.447l-1.556-.52a6.973 6.973 0 01-1.43.824l-.331 1.652a1 1 0 01-.98.804H8.82a1 1 0 01-.98-.804l-.331-1.652a6.973 6.973 0 01-1.43-.824l-1.556.52a1 1 0 01-1.144-.447l-1.18-2.043a1 1 0 01.163-1.216L3.587 11.4a7.11 7.11 0 010-1.648L2.362 8.62a1 1 0 01-.163-1.216l1.18-2.043a1 1 0 011.144-.447l1.556.52a6.973 6.973 0 011.43-.824l.331-1.652zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif
        </div>
    </x-slot>

    <div
        x-data="financeDashboard({
            chartPresets: @js($chartPresets),
            groupedRecords: @js($groupedRecords),
            monthStatuses: @js($monthStatuses),
            todayDate: @js($todayDate),
            storeRecordUrl: @js(route('finance.records.store')),
            updateCarryBalanceUrl: @js(route('finance.carry-balance.update')),
        })"
        x-init="
            @if ($errors->has('user_ids') || $errors->has('user_ids.*'))
                accessModalOpen = true;
            @endif
        "
        x-on:open-finance-access-modal.window="accessModalOpen = true"
        class="py-12"
    >
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status') === 'finance-access-updated')
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ __('Finance access updated.') }}
                </div>
            @elseif (session('status') === 'finance-access-unavailable')
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ __('Finance access storage is not ready yet. Run `php artisan migrate` first.') }}
                </div>
            @endif

            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-slate-200 p-6">
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)] xl:items-start">
                        <div class="max-w-3xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#87A1CC]">{{ __('Overview') }}</p>
                            <h3 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ __('Your month at a glance') }}</h3>
                            <p class="mt-4 text-base leading-8 text-slate-500">
                                {{ __('See your salary, your balance before payday, your planned bills, and what should still be left after them.') }}
                            </p>
                        </div>

                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div class="grid gap-6 xl:grid-cols-[20rem_minmax(0,1fr)]">
                        <aside class="space-y-5">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Summary') }}</p>
                                <p class="mt-3 text-sm leading-7 text-slate-500" x-text="summaryText"></p>

                                <div class="mt-5 space-y-3 border-t border-slate-200 pt-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Salary This Month') }}</span>
                                        <span class="text-base font-semibold text-[#5A8DEE]" x-text="currency(selectedMonthSalary)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Planned Bills') }}</span>
                                        <span class="text-sm font-semibold text-rose-500" x-text="currency(-selectedMonthCommitmentTotal)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Balance After Bills') }}</span>
                                        <span class="text-sm font-semibold" :class="selectedMonthBalanceAfterBills !== null && selectedMonthBalanceAfterBills >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="selectedMonthBalanceAfterBills === null ? '' : currency(selectedMonthBalanceAfterBills)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Balance Before Salary') }}</span>
                                        <span class="text-sm font-semibold text-slate-900" x-text="selectedMonthCarryBalanceDisplay"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('Month') }}</p>
                                <div class="mt-4 space-y-3">
                                    <select
                                        x-model="selectedMonthId"
                                        class="w-full rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm focus:border-slate-400 focus:ring-slate-400"
                                    >
                                        <template x-for="month in monthStatuses" :key="month.id">
                                            <option :value="month.id" x-text="month.label"></option>
                                        </template>
                                    </select>
                                    <p class="text-sm text-slate-500" x-text="selectedMonth.salary_date || 'No salary date saved yet'"></p>
                                </div>
                            </div>
                        </aside>

                        <div class="space-y-6">
                            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#87A1CC]">{{ __('Chart') }}</p>
                                        <h4 class="mt-2 text-xl font-semibold text-slate-950">{{ __('Cash flow') }}</h4>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-slate-500">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                                            {{ __('Balance') }}
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full bg-[#5A8DEE]"></span>
                                            {{ __('Income') }}
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-3 w-3 rounded-full bg-[#FF4F70]"></span>
                                            {{ __('Bills Paid') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-6 rounded-[1.75rem] border border-slate-100 bg-white/90 p-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm text-slate-500" x-text="activePreset.range"></p>
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-slate-400">{{ __('Monthly view') }}</p>
                                    </div>

                                    <div class="mt-4 grid grid-cols-[4.25rem_minmax(0,1fr)] gap-4">
                                        <div class="flex h-[18rem] flex-col justify-between text-sm font-medium text-[#8AA0C8]">
                                            <template x-for="mark in chartAxisMarks" :key="'axis-' + mark">
                                                <span x-text="currency(mark)"></span>
                                            </template>
                                        </div>

                                        <div>
                                            <div class="relative h-[18rem] overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white">
                                                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_bottom,rgba(148,163,184,0.16)_1px,transparent_1px)] bg-[length:100%_25%]"></div>
                                                <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-slate-300/70" :style="`top:${chartZeroYPct}%`"></div>

                                                <svg viewBox="0 0 960 300" class="pointer-events-none absolute inset-0 h-full w-full">
                                                    <path
                                                        :d="endingBalanceLinePath"
                                                        fill="none"
                                                        stroke="#2FD39B"
                                                        stroke-width="4"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    ></path>

                                                    <template x-for="point in salaryPeakPoints" :key="'peak-dot-' + point.label">
                                                        <circle
                                                            :cx="point.x"
                                                            :cy="point.y"
                                                            r="5"
                                                            fill="white"
                                                            stroke="#2FD39B"
                                                            stroke-width="3"
                                                            opacity="0.92"
                                                        ></circle>
                                                    </template>
                                                </svg>

                                                <template x-for="column in chartColumns" :key="'income-bar-' + column.label">
                                                    <div
                                                        class="pointer-events-none absolute z-10 rounded-t-xl bg-[#5A8DEE]/90 shadow-[0_10px_24px_rgba(90,141,238,0.18)]"
                                                        :style="`left:${column.incomeLeftPct}%; top:${column.incomeTopPct}%; width:${column.barWidthPct}%; height:${column.incomeHeightPct}%;`"
                                                    ></div>
                                                </template>

                                                <template x-for="column in chartColumns" :key="'income-label-' + column.label">
                                                    <div
                                                        class="pointer-events-none absolute z-20 -translate-x-1/2 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-[#5A8DEE] shadow-sm ring-1 ring-slate-200"
                                                        :style="`left:${column.incomeCenterPct}%; top:${column.incomeLabelTopPct}%;`"
                                                        x-text="compactCurrency(column.incomeValue)"
                                                    ></div>
                                                </template>

                                                <template x-for="column in chartColumns" :key="'spending-bar-' + column.label">
                                                    <div
                                                        class="pointer-events-none absolute z-10 rounded-t-xl bg-[#FF4F70]/90 shadow-[0_10px_24px_rgba(255,79,112,0.16)]"
                                                        :style="`left:${column.spendingLeftPct}%; top:${column.spendingTopPct}%; width:${column.barWidthPct}%; height:${column.spendingHeightPct}%;`"
                                                    ></div>
                                                </template>

                                                <template x-for="column in chartColumns" :key="'spending-label-' + column.label">
                                                    <div
                                                        class="pointer-events-none absolute z-20 -translate-x-1/2 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-[#FF4F70] shadow-sm ring-1 ring-slate-200"
                                                        :style="`left:${column.spendingCenterPct}%; top:${column.spendingLabelTopPct}%;`"
                                                        x-text="compactCurrency(-column.spendingValue)"
                                                    ></div>
                                                </template>

                                                <template x-for="point in endingBalancePoints" :key="'dot-' + point.label">
                                                    <div
                                                        class="pointer-events-none absolute z-20 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-emerald-400 bg-white"
                                                        :style="`left:${point.xPct}%; top:${point.yPct}%;`"
                                                    ></div>
                                                </template>

                                                <template x-for="point in endingBalancePoints" :key="'point-label-' + point.label">
                                                    <div
                                                        class="pointer-events-none absolute z-20 -translate-x-1/2 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold shadow-sm ring-1 ring-emerald-200"
                                                        :class="point.endingBalance >= 0 ? 'text-emerald-700' : 'text-rose-600 ring-rose-200 bg-rose-50'"
                                                        :style="`left:${point.xPct}%; top:${point.labelTopPct}%;`"
                                                        x-text="compactCurrency(point.endingBalance)"
                                                    ></div>
                                                </template>
                                            </div>

                                            <div class="mt-4 grid gap-2 text-center text-sm font-semibold text-[#8AA0C8]" :style="`grid-template-columns: repeat(${activePreset.labels.length}, minmax(0, 1fr))`">
                                                <template x-for="label in activePreset.labels" :key="'month-' + label">
                                                    <span x-text="label"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)]">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#87A1CC]">{{ __('Bills') }}</p>
                                    <h3 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">{{ __('This month\'s payment status') }}</h3>
                                    <p class="mt-3 text-base leading-7 text-slate-500">{{ __('See which bills are paid, which are still waiting, and how much is still outstanding.') }}</p>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap xl:justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:w-auto"
                                        x-on:click="openCarryModal()"
                                    >
                                        {{ __('Update End Balance') }}
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto"
                                        x-on:click="openRecordModal('spending')"
                                    >
                                        {{ __('+ Add Bill') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 lg:grid-cols-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Selected Month') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-950" x-text="selectedMonth.label"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonth.salary_date || 'No salary date saved yet'"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Bills Paid') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-emerald-600" x-text="currency(-selectedMonthPaidTotal)"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthPaidCount + ' bills paid'"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Bills Not Paid Yet') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-amber-600" x-text="currency(-selectedMonthUnpaidTotal)"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthUnpaidCount + ' bills not paid yet'"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Previous Month') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-950" x-text="previousMonth ? previousMonth.label : 'No previous month'"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="previousMonth ? currency(previousMonthPendingTotal) + ' not paid yet' : 'Nothing to compare yet'"></p>
                                </div>
                            </div>

                            <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
                                <div class="hidden grid-cols-[minmax(14rem,1.45fr)_9rem_8.5rem_9rem_10rem] gap-6 border-b border-slate-200 bg-slate-50 px-6 py-4 text-sm font-semibold uppercase tracking-[0.16em] text-slate-500 xl:grid">
                                    <span>{{ __('Commitment') }}</span>
                                    <span>{{ __('Status') }}</span>
                                    <span>{{ __('Amount') }}</span>
                                    <span>{{ __('Paid On') }}</span>
                                    <span class="text-right">{{ __('Action') }}</span>
                                </div>

                                <div class="hidden divide-y divide-slate-200 xl:block">
                                    <template x-for="commitment in selectedMonth.commitments" :key="`${selectedMonth.id}-${commitment.category}`">
                                        <div class="grid grid-cols-[minmax(14rem,1.45fr)_9rem_8.5rem_9rem_10rem] gap-6 bg-white px-6 py-5 items-center">
                                            <div class="min-w-0">
                                                <p class="text-lg font-semibold text-slate-950" x-text="commitment.category"></p>
                                                <p class="mt-1 max-w-xs text-sm leading-6 text-slate-500" x-text="commitment.status === 'paid' ? 'Already settled for this month' : 'Still waiting to be paid this month'"></p>
                                            </div>

                                            <div class="min-w-0">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]"
                                                    :class="commitment.status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                                    x-text="commitment.status"
                                                ></span>
                                            </div>

                                            <div class="min-w-0 text-sm font-semibold text-slate-900" x-text="currency(-commitment.amount)"></div>
                                            <div class="min-w-0 text-sm text-slate-500" x-text="commitment.paid_on ?? 'Pending'"></div>

                                            <div class="text-right">
                                                <button
                                                    type="button"
                                                    class="inline-flex min-w-[8.5rem] justify-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold transition"
                                                    :class="commitment.status === 'paid' ? 'bg-white text-slate-600 hover:bg-slate-50' : 'bg-slate-950 text-white hover:bg-slate-800'"
                                                    x-on:click="toggleCommitmentStatus(selectedMonth.id, commitment.category)"
                                                    x-text="commitment.status === 'paid' ? 'Mark Unpaid' : 'Mark Paid'"
                                                ></button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="divide-y divide-slate-200 lg:hidden">
                                    <template x-for="commitment in selectedMonth.commitments" :key="`mobile-${selectedMonth.id}-${commitment.category}`">
                                        <div class="space-y-4 bg-white px-5 py-5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-base font-semibold text-slate-950" x-text="commitment.category"></p>
                                                    <p class="mt-1 text-sm text-slate-500" x-text="commitment.status === 'paid' ? 'Already settled for this month' : 'Still waiting to be paid this month'"></p>
                                                </div>
                                                <span
                                                    class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]"
                                                    :class="commitment.status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                                    x-text="commitment.status"
                                                ></span>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Amount') }}</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="currency(-commitment.amount)"></p>
                                                </div>
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Paid On') }}</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="commitment.paid_on ?? 'Pending'"></p>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 px-3 py-2 text-sm font-semibold transition"
                                                :class="commitment.status === 'paid' ? 'bg-white text-slate-600 hover:bg-slate-50' : 'bg-slate-950 text-white hover:bg-slate-800'"
                                                x-on:click="toggleCommitmentStatus(selectedMonth.id, commitment.category)"
                                                x-text="commitment.status === 'paid' ? 'Mark Unpaid' : 'Mark Paid'"
                                            ></button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#87A1CC]">{{ __('Quick View') }}</p>
                                <h4 class="mt-3 text-xl font-semibold text-slate-950">{{ __('This month and last month') }}</h4>

                                <div class="mt-6 space-y-4">
                                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Paid This Month') }}</p>
                                        <p class="mt-2 text-2xl font-semibold text-emerald-600" x-text="currency(-selectedMonthPaidTotal)"></p>
                                        <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthPaidCount + ' bills paid'"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Still Waiting') }}</p>
                                        <p class="mt-2 text-2xl font-semibold text-amber-600" x-text="currency(-selectedMonthUnpaidTotal)"></p>
                                        <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthUnpaidCount + ' bills not paid yet'"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Paid Last Month') }}</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="previousMonth ? currency(-previousMonthPaidTotal) : 'RM0'"></p>
                                        <p class="mt-1 text-sm text-slate-500" x-text="previousMonth ? previousMonth.label : 'No earlier month yet'"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Total Spending This Month') }}</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="selectedMonthTotalSpending === null ? '' : currency(selectedMonthTotalSpending)"></p>
                                        <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthTotalSpending === null ? 'Waiting for salary to be recorded for this month.' : 'Balance after bills minus balance before salary.'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <template x-if="recordModalOpen">
            <div class="fixed inset-0 z-40 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="closeRecordModal()"></div>
                <div class="relative z-10 w-full max-w-2xl rounded-[2rem] bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('Monthly Record') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-slate-950">{{ __('Add a bill or salary') }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ __('Add your salary for the month or add one of your monthly bills.') }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                            x-on:click="closeRecordModal()"
                        >
                            {{ __('Close') }}
                        </button>
                    </div>

                    <div class="mt-6 inline-flex rounded-full border border-slate-200 bg-slate-50 p-1">
                        <button
                            type="button"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition"
                            :class="recordForm.type === 'spending' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500'"
                            x-on:click="setRecordType('spending')"
                        >
                            {{ __('Spending') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition"
                            :class="recordForm.type === 'income' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500'"
                            x-on:click="setRecordType('income')"
                        >
                            {{ __('Income') }}
                        </button>
                    </div>

                    <div class="mt-6 space-y-5">
                        <template x-if="recordForm.type === 'income'">
                            <div class="grid gap-5 md:grid-cols-2">
                                <label class="block">
                                    <span class="text-sm font-medium text-slate-700">{{ __('Date') }}</span>
                                    <input type="date" x-model="recordForm.date" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                </label>
                                <label class="block">
                                    <span class="text-sm font-medium text-slate-700">{{ __('Salary / Income Value') }}</span>
                                    <input type="number" min="0" step="1" x-model.number="recordForm.value" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="3200">
                                </label>
                            </div>
                        </template>

                        <template x-if="recordForm.type === 'spending'">
                            <div class="space-y-5">
                                <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_10rem_12rem]">
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">{{ __('Commitment Category') }}</span>
                                        <select x-model="recordForm.category" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                            <template x-for="category in spendingCategories" :key="'option-' + category">
                                                <option :value="category" x-text="category"></option>
                                            </template>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">{{ __('Value') }}</span>
                                        <input type="number" min="0" step="1" x-model.number="recordForm.value" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="250">
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-medium text-slate-700">{{ __('Date') }}</span>
                                        <input type="date" x-model="recordForm.date" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                    </label>
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                                    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                                        <label class="block">
                                            <span class="text-sm font-medium text-slate-700">{{ __('Add New Category') }}</span>
                                            <input type="text" x-model="newCategoryName" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="eg: Car loan">
                                        </label>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                                            x-on:click="addCategory()"
                                        >
                                            {{ __('Add Category') }}
                                        </button>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <template x-for="category in spendingCategories" :key="'chip-' + category">
                                            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                                <span x-text="category"></span>
                                                <button
                                                    type="button"
                                                    class="text-slate-400 transition hover:text-rose-500"
                                                    x-on:click="removeCategory(category)"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/80 px-4 py-4 text-sm text-slate-500">
                        <template x-if="recordForm.type === 'income'">
                            <p>{{ __('Income mode is for the salary amount inserted on salary day. Carry-over balance is updated separately.') }}</p>
                        </template>
                        <template x-if="recordForm.type === 'spending'">
                            <p>{{ __('Spending mode is only for planned monthly commitments like rent, bills, wifi, subscriptions, or a flexible monthly buffer.') }}</p>
                        </template>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                            x-on:click="closeRecordModal()"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full bg-slate-950 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                            x-on:click="submitRecord()"
                        >
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="carryModalOpen">
            <div class="fixed inset-0 z-40 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="closeCarryModal()"></div>
                <div class="relative z-10 w-full max-w-xl rounded-[2rem] bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('End Month Balance') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-slate-950">{{ __('Update balance before salary') }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ __('Enter how much money you still had before the next salary came in.') }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                            x-on:click="closeCarryModal()"
                        >
                            {{ __('Close') }}
                        </button>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">{{ __('Date') }}</span>
                            <input type="date" x-model="carryForm.date" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </label>
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">{{ __('Carry-Over Balance') }}</span>
                            <input type="number" min="0" step="1" x-model.number="carryForm.value" class="mt-2 w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="300">
                        </label>
                    </div>

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/80 px-4 py-4 text-sm text-slate-500">
                        <p>{{ __('Example: if you had RM50 left before February salary came in, enter RM50 here.') }}</p>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                            x-on:click="closeCarryModal()"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full bg-slate-950 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                            x-on:click="submitCarryBalance()"
                        >
                            {{ __('Save End Balance') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        @if ($canManageAccess)
            <template x-if="accessModalOpen">
                <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                    <div class="absolute inset-0 bg-slate-900/40" x-on:click="accessModalOpen = false"></div>
                    <div class="relative z-10 w-full max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-slate-500">{{ __('Access Settings') }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Finance Page Access') }}</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ __('Choose which registered users can open the Finance page.') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="rounded-full border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                                x-on:click="accessModalOpen = false"
                            >
                                {{ __('Close') }}
                            </button>
                        </div>

                        @if ($accessTableReady)
                            <form method="POST" action="{{ route('finance.access.update') }}" class="mt-6 space-y-4">
                                @csrf
                                @method('PUT')

                                <div class="grid gap-3 sm:grid-cols-2">
                                    @forelse ($users as $managedUser)
                                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 px-4 py-3">
                                            <input
                                                type="checkbox"
                                                name="user_ids[]"
                                                value="{{ $managedUser->id }}"
                                                @disabled(in_array($managedUser->id, $alwaysAllowedUserIds, true))
                                                @checked(in_array($managedUser->id, old('user_ids', $selectedUserIds), true))
                                                @checked(in_array($managedUser->id, $alwaysAllowedUserIds, true))
                                                class="mt-1 rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-500"
                                            >
                                            <span>
                                                <span class="block text-sm font-medium text-gray-900">
                                                    {{ $managedUser->name }}
                                                    @if (in_array($managedUser->id, $alwaysAllowedUserIds, true))
                                                        <span class="ml-2 text-xs font-medium text-gray-500">{{ __('Always allowed') }}</span>
                                                    @endif
                                                </span>
                                                <span class="block text-sm text-gray-500">{{ $managedUser->email }}</span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500">{{ __('No registered users found.') }}</p>
                                    @endforelse
                                </div>

                                <x-input-error class="mt-2" :messages="$errors->get('user_ids')" />
                                <x-input-error class="mt-2" :messages="$errors->get('user_ids.*')" />

                                <div class="flex items-center gap-3">
                                    <x-primary-button>{{ __('Save Access') }}</x-primary-button>
                                    <p class="text-xs text-gray-500">{{ __('Admins will still be able to open this page.') }}</p>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </template>
        @endif
    </div>

    <script>
        function financeDashboard(config) {
            return {
                accessModalOpen: false,
                recordModalOpen: false,
                carryModalOpen: false,
                period: 'monthly',
                chartPresets: config.chartPresets,
                groupedRecords: config.groupedRecords,
                monthStatuses: config.monthStatuses,
                selectedMonthId: config.monthStatuses[0]?.id ?? null,
                todayDate: config.todayDate,
                storeRecordUrl: config.storeRecordUrl,
                updateCarryBalanceUrl: config.updateCarryBalanceUrl,
                newCategoryName: '',
                recordForm: {
                    type: 'spending',
                    date: config.todayDate,
                    value: '',
                    category: 'Rent',
                },
                carryForm: {
                    date: config.todayDate,
                    value: '',
                },
                get activePreset() {
                    return this.chartPresets[this.period] ?? this.chartPresets.monthly;
                },
                get selectedMonthIndex() {
                    return this.monthStatuses.findIndex((month) => month.id === this.selectedMonthId);
                },
                get selectedMonth() {
                    return this.monthStatuses[this.selectedMonthIndex] ?? this.monthStatuses[0] ?? {
                        id: '',
                        label: '',
                        salary_date: '',
                        salary: 0,
                        opening_balance: 0,
                        balance_after_bills: null,
                        has_salary: false,
                        has_closing_balance: false,
                        is_current_period: false,
                        carry_balance: 0,
                        commitments: [],
                    };
                },
                get previousMonth() {
                    const index = this.selectedMonthIndex;
                    if (index < 0 || index === this.monthStatuses.length - 1) {
                        return null;
                    }

                    return this.monthStatuses[index + 1];
                },
                get selectedMonthSalary() {
                    return this.selectedMonth.salary ?? 0;
                },
                get selectedMonthOpeningBalance() {
                    return this.selectedMonth.opening_balance ?? this.selectedMonthCarryBalance;
                },
                get selectedMonthCarryBalance() {
                    return this.selectedMonth.carry_balance ?? 0;
                },
                get selectedMonthCarryBalanceDisplay() {
                    return this.selectedMonthNeedsClosingBalance ? 'TBA' : this.currency(this.selectedMonthCarryBalance);
                },
                get selectedMonthNeedsClosingBalance() {
                    return this.selectedMonth.is_current_period && !this.selectedMonth.has_closing_balance;
                },
                get selectedMonthStartingBalance() {
                    return this.selectedMonthOpeningBalance + this.selectedMonthSalary;
                },
                get selectedMonthCommitmentTotal() {
                    return this.selectedMonth.commitments.reduce((sum, item) => sum + item.amount, 0);
                },
                get selectedMonthBalanceAfterBills() {
                    if (!this.selectedMonth.has_salary) {
                        return null;
                    }

                    return this.selectedMonth.balance_after_bills ?? (this.selectedMonthStartingBalance - this.selectedMonthCommitmentTotal);
                },
                get selectedMonthTotalSpending() {
                    if (this.selectedMonthBalanceAfterBills === null || this.selectedMonthNeedsClosingBalance) {
                        return null;
                    }

                    return this.selectedMonthBalanceAfterBills - this.selectedMonthCarryBalance;
                },
                get carryBalance() {
                    return this.activePreset.ending_balance?.[this.activePreset.ending_balance.length - 1] ?? 0;
                },
                get currentBalance() {
                    return this.activePreset.starting_balance?.[this.activePreset.starting_balance.length - 1] ?? 0;
                },
                get totalIncome() {
                    return this.activePreset.income.reduce((sum, value) => sum + value, 0);
                },
                get totalSpending() {
                    return this.activePreset.spending.reduce((sum, value) => sum + value, 0);
                },
                get totalCommittedSpending() {
                    return this.totalSpending;
                },
                get currentCommittedSpending() {
                    return this.activePreset.spending[this.activePreset.spending.length - 1] ?? 0;
                },
                get selectedMonthPaidItems() {
                    return this.selectedMonth.commitments.filter((item) => item.status === 'paid');
                },
                get selectedMonthUnpaidItems() {
                    return this.selectedMonth.commitments.filter((item) => item.status !== 'paid');
                },
                get selectedMonthPaidTotal() {
                    return this.selectedMonthPaidItems.reduce((sum, item) => sum + item.amount, 0);
                },
                get selectedMonthUnpaidTotal() {
                    return this.selectedMonthUnpaidItems.reduce((sum, item) => sum + item.amount, 0);
                },
                get selectedMonthPaidCount() {
                    return this.selectedMonthPaidItems.length;
                },
                get selectedMonthUnpaidCount() {
                    return this.selectedMonthUnpaidItems.length;
                },
                get previousMonthPaidTotal() {
                    return this.previousMonth
                        ? this.previousMonth.commitments.filter((item) => item.status === 'paid').reduce((sum, item) => sum + item.amount, 0)
                        : 0;
                },
                get previousMonthPendingTotal() {
                    return this.previousMonth
                        ? this.previousMonth.commitments.filter((item) => item.status !== 'paid').reduce((sum, item) => sum + item.amount, 0)
                        : 0;
                },
                get plannedRemaining() {
                    return this.currentBalance - this.currentCommittedSpending;
                },
                get netFlow() {
                    return this.plannedRemaining;
                },
                get chartDataPoints() {
                    return this.activePreset.labels.map((label, index) => ({
                        index,
                        label,
                        openingBalance: this.activePreset.opening_balance?.[index] ?? 0,
                        income: this.activePreset.income?.[index] ?? 0,
                        spending: this.activePreset.spending?.[index] ?? 0,
                        startingBalance: this.activePreset.starting_balance?.[index] ?? 0,
                        balanceAfterBills: this.activePreset.balance_after_bills?.[index] ?? null,
                        endingBalance: this.activePreset.ending_balance?.[index] ?? 0,
                    }));
                },
                get summaryText() {
                    const remaining = this.selectedMonthBalanceAfterBills;

                    if (!this.selectedMonth.id) {
                        return 'No month selected yet.';
                    }

                    if (!this.selectedMonth.has_salary) {
                        return `For ${this.selectedMonth.label}, salary has not been recorded yet. You can list bills now, and the after-bills and total-spending figures will appear once salary is added.`;
                    }

                    if (this.selectedMonthNeedsClosingBalance) {
                        return `For ${this.selectedMonth.label}, salary and bills are recorded, but the real balance before the next salary has not been updated yet. The closing balance and total spending will stay as TBA until you enter that value.`;
                    }

                    if (this.selectedMonthCommitmentTotal === 0) {
                        return `For ${this.selectedMonth.label}, you started from ${this.currency(this.selectedMonthOpeningBalance)}, added salary of ${this.currency(this.selectedMonthSalary)}, and have not added any bills yet.`;
                    }

                    if (remaining >= 0) {
                        if (this.selectedMonthTotalSpending === null) {
                            return `For ${this.selectedMonth.label}, you started from ${this.currency(this.selectedMonthOpeningBalance)}, added salary of ${this.currency(this.selectedMonthSalary)}, and should still have ${this.currency(remaining)} left after ${this.currency(-this.selectedMonthCommitmentTotal)} in bills.`;
                        }

                        return `For ${this.selectedMonth.label}, you started from ${this.currency(this.selectedMonthOpeningBalance)}, added salary of ${this.currency(this.selectedMonthSalary)}, should still have ${this.currency(remaining)} after ${this.currency(-this.selectedMonthCommitmentTotal)} in bills, and your real balance before the next salary is ${this.currency(this.selectedMonthCarryBalance)}.`;
                    }

                    return `For ${this.selectedMonth.label}, you started from ${this.currency(this.selectedMonthOpeningBalance)}, added salary of ${this.currency(this.selectedMonthSalary)}, and your bills of ${this.currency(-this.selectedMonthCommitmentTotal)} leave you short by ${this.currency(Math.abs(remaining))} before other spending is considered.`;
                },
                get groupedRecordShareBase() {
                    return this.groupedRecords
                        .filter((group) => group.type !== 'balance')
                        .reduce((sum, group) => sum + Math.abs(group.total), 0) || 1;
                },
                get spendingCategories() {
                    return this.groupedRecords
                        .filter((group) => group.type === 'spending')
                        .map((group) => group.category)
                        .sort((a, b) => a.localeCompare(b));
                },
                get spendingCategoryCount() {
                    return this.spendingCategories.length;
                },
                get topIncomeCategory() {
                    return this.groupedRecords
                        .filter((group) => group.type === 'income' && group.total > 0)
                        .sort((a, b) => b.total - a.total)[0] ?? { category: 'None', total: 0 };
                },
                get topSpendingCategory() {
                    return this.groupedRecords
                        .filter((group) => group.type === 'spending' && group.total < 0)
                        .sort((a, b) => a.total - b.total)[0] ?? { category: 'None', total: 0 };
                },
                get groupedRecordsWithShare() {
                    return this.groupedRecords
                        .filter((group) => group.type !== 'balance')
                        .map((group) => ({
                            ...group,
                            share: Math.max(10, Math.round((Math.abs(group.total) / this.groupedRecordShareBase) * 100)),
                        }));
                },
                get bestPoint() {
                    const pairs = this.activePreset.labels.map((label, index) => ({
                        label,
                        balance: this.activePreset.ending_balance?.[index] ?? 0,
                    }));

                    return pairs.sort((a, b) => b.balance - a.balance)[0] ?? { label: '-', balance: 0 };
                },
                get highestSpendingPoint() {
                    const pairs = this.activePreset.labels.map((label, index) => ({
                        label,
                        spending: this.activePreset.spending[index] ?? 0,
                    }));

                    return pairs.sort((a, b) => b.spending - a.spending)[0] ?? { label: '-', spending: 0 };
                },
                metricWidth(value, max) {
                    if (!max) {
                        return 24;
                    }

                    return Math.min(100, Math.max(24, (Math.abs(value) / max) * 100));
                },
                currency(value) {
                    const abs = Math.abs(value);
                    return `${value < 0 ? '-RM' : 'RM'}${abs.toLocaleString()}`;
                },
                compactCurrency(value) {
                    const abs = Math.abs(value);
                    const formatted = abs >= 1000
                        ? `${(abs / 1000).toFixed(abs % 1000 === 0 ? 0 : 1)}k`
                        : abs.toLocaleString();

                    return `${value < 0 ? '-RM' : 'RM'}${formatted}`;
                },
                setRecordType(type) {
                    this.recordForm.type = type;
                    this.recordForm.date = this.todayDate;
                    this.recordForm.value = '';
                    this.recordForm.category = type === 'spending'
                        ? (this.recordForm.category || this.spendingCategories[0] || 'Rent')
                        : 'Salary';
                },
                openRecordModal(type = 'spending', category = '') {
                    this.recordModalOpen = true;
                    this.setRecordType(type);

                    if (type === 'spending') {
                        this.recordForm.category = category || this.spendingCategories[0] || 'Rent';
                    }
                },
                closeRecordModal() {
                    this.recordModalOpen = false;
                    this.newCategoryName = '';
                    this.setRecordType('spending');
                },
                openCarryModal() {
                    this.carryModalOpen = true;
                    this.carryForm.date = this.todayDate;
                    this.carryForm.value = this.selectedMonthNeedsClosingBalance ? '' : this.selectedMonthCarryBalance;
                },
                closeCarryModal() {
                    this.carryModalOpen = false;
                    this.carryForm.date = this.todayDate;
                    this.carryForm.value = '';
                },
                addCategory() {
                    const name = this.newCategoryName.trim();
                    if (!name) {
                        return;
                    }

                    const exists = this.groupedRecords.some((group) => group.category.toLowerCase() === name.toLowerCase());

                    if (!exists) {
                        this.groupedRecords.push({
                            category: name,
                            icon: name.charAt(0).toUpperCase(),
                            accent: '#7C8DB5',
                            type: 'spending',
                            entries: [],
                            count: 0,
                            total: 0,
                        });
                    }

                    this.recordForm.category = name;
                    this.newCategoryName = '';
                },
                removeCategory(category) {
                    this.groupedRecords = this.groupedRecords.filter((group) => group.category !== category);

                    if (this.recordForm.category === category) {
                        this.recordForm.category = this.spendingCategories[0] || 'Rent';
                    }
                },
                async submitRecord() {
                    const amount = Number(this.recordForm.value);

                    if (!amount || amount < 0) {
                        return;
                    }

                    if (this.recordForm.type === 'spending' && !(this.recordForm.category || '').trim()) {
                        return;
                    }

                    try {
                        await window.axios.post(this.storeRecordUrl, {
                            type: this.recordForm.type,
                            date: this.recordForm.date,
                            value: amount,
                            category: this.recordForm.type === 'spending' ? this.recordForm.category : null,
                        });

                        window.location.reload();
                    } catch (error) {
                        console.error(error);
                        window.alert('Unable to save this finance record right now.');
                    }
                },
                async submitCarryBalance() {
                    const amount = Number(this.carryForm.value);

                    if (amount < 0 || Number.isNaN(amount)) {
                        return;
                    }

                    try {
                        await window.axios.put(this.updateCarryBalanceUrl, {
                            date: this.carryForm.date,
                            value: amount,
                        });

                        window.location.reload();
                    } catch (error) {
                        console.error(error);
                        window.alert('Unable to save the balance before salary right now.');
                    }
                },
                toggleCommitmentStatus(monthId, category) {
                    const month = this.monthStatuses.find((item) => item.id === monthId);
                    const commitment = month?.commitments.find((item) => item.category === category);

                    if (!commitment) {
                        return;
                    }

                    if (commitment.status === 'paid') {
                        commitment.status = 'unpaid';
                        commitment.paid_on = null;
                        return;
                    }

                    commitment.status = 'paid';
                    commitment.paid_on = this.formatDate(this.todayDate);
                },
                applyChartImpact(type, amount) {
                    Object.values(this.chartPresets).forEach((preset) => {
                        const lastIndex = preset.starting_balance.length - 1;

                        if (type === 'carry') {
                            preset.ending_balance[lastIndex] = amount;
                            return;
                        }

                        if (type === 'income') {
                            preset.income[lastIndex] = (preset.income[lastIndex] ?? 0) + amount;
                            preset.starting_balance[lastIndex] = (preset.opening_balance[lastIndex] ?? 0) + (preset.income[lastIndex] ?? 0);
                            preset.balance_after_bills[lastIndex] = preset.starting_balance[lastIndex] - (preset.spending[lastIndex] ?? 0);
                            return;
                        }

                        preset.spending[lastIndex] = (preset.spending[lastIndex] ?? 0) + amount;
                        preset.balance_after_bills[lastIndex] = (preset.starting_balance[lastIndex] ?? 0) - preset.spending[lastIndex];
                    });
                },
                findGroup(category) {
                    return this.groupedRecords.find((group) => group.category === category);
                },
                upsertRecordGroup({ category, icon, accent, type, entry, replaceLatest = false }) {
                    const existing = this.findGroup(category);

                    if (existing) {
                        if (replaceLatest && existing.entries.length) {
                            existing.entries[existing.entries.length - 1] = entry;
                        } else {
                            existing.entries.unshift(entry);
                        }
                        existing.count = existing.entries.length;
                        existing.total = existing.entries.reduce((sum, current) => sum + current.amount, 0);
                        return;
                    }

                    this.groupedRecords.unshift({
                        category,
                        icon,
                        accent,
                        type,
                        entries: [entry],
                        count: 1,
                        total: entry.amount,
                    });
                },
                formatDate(dateString) {
                    const date = new Date(dateString);
                    if (Number.isNaN(date.getTime())) {
                        return dateString;
                    }

                    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                },
                chartMetrics() {
                    return {
                        width: 960,
                        height: 300,
                        left: 44,
                        right: 916,
                        top: 18,
                        bottom: 256,
                    };
                },
                chartY(value) {
                    const metrics = this.chartMetrics();
                    const range = this.chartDomainMax - this.chartDomainMin || 1;
                    const plotHeight = metrics.bottom - metrics.top;

                    return Number((metrics.bottom - (((value - this.chartDomainMin) / range) * plotHeight)).toFixed(2));
                },
                barRect(value, x, width) {
                    const zeroY = this.chartZeroY;
                    const valueY = this.chartY(value);
                    const y = Math.min(zeroY, valueY);
                    const height = Math.abs(zeroY - valueY);

                    return {
                        x: Number(x.toFixed(2)),
                        y: Number(y.toFixed(2)),
                        width: Number(width.toFixed(2)),
                        height: Number(height.toFixed(2)),
                    };
                },
                buildLinePath(points) {
                    if (!points.length) {
                        return '';
                    }

                    if (points.length === 1) {
                        return `M ${points[0].x} ${points[0].y}`;
                    }

                    let path = `M ${points[0].x} ${points[0].y}`;

                    for (let i = 0; i < points.length - 1; i++) {
                        const current = points[i];
                        const next = points[i + 1];
                        const controlX = Number(((current.x + next.x) / 2).toFixed(2));
                        path += ` C ${controlX} ${current.y}, ${controlX} ${next.y}, ${next.x} ${next.y}`;
                    }

                    return path;
                },
                get chartDomainMin() {
                    const minValue = Math.min(
                        0,
                        ...this.chartDataPoints.flatMap((point) => [
                            point.openingBalance,
                            point.startingBalance,
                            point.balanceAfterBills ?? 0,
                            point.endingBalance,
                        ]),
                    );
                    return Math.floor(minValue / 250) * 250;
                },
                get chartDomainMax() {
                    const maxValue = Math.max(
                        0,
                        ...this.chartDataPoints.flatMap((point) => [
                            point.openingBalance,
                            point.income,
                            point.spending,
                            point.startingBalance,
                            point.balanceAfterBills ?? 0,
                            point.endingBalance,
                        ]),
                    );

                    return Math.max(250, Math.ceil(maxValue / 250) * 250);
                },
                get chartAxisMarks() {
                    const steps = 4;
                    const range = this.chartDomainMax - this.chartDomainMin;

                    return Array.from({ length: steps + 1 }, (_, index) => {
                        const value = this.chartDomainMax - ((range / steps) * index);
                        return Math.round(value);
                    });
                },
                get chartGridLines() {
                    return this.chartAxisMarks.map((value) => ({
                        value,
                        y: this.chartY(value),
                    }));
                },
                get chartZeroY() {
                    return this.chartY(0);
                },
                get chartZeroYPct() {
                    return Number(((this.chartZeroY / this.chartMetrics().height) * 100).toFixed(2));
                },
                get chartColumns() {
                    const metrics = this.chartMetrics();
                    const plotWidth = metrics.right - metrics.left;
                    const count = Math.max(this.chartDataPoints.length, 1);
                    const columnWidth = plotWidth / count;
                    const barWidth = Math.max(16, Math.min(26, columnWidth * 0.18));
                    const gap = Math.max(10, Math.min(18, columnWidth * 0.1));

                    return this.chartDataPoints.map((point, index) => {
                        const centerX = metrics.left + (columnWidth * index) + (columnWidth / 2);
                        const incomeRect = this.barRect(point.income, centerX - gap - barWidth, barWidth);
                        const spendingRect = this.barRect(point.spending, centerX + gap, barWidth);

                        return {
                            index,
                            label: point.label,
                            centerX: Number(centerX.toFixed(2)),
                            centerXPct: Number(((centerX / metrics.width) * 100).toFixed(2)),
                            columnWidth: Number(columnWidth.toFixed(2)),
                            barWidthPct: Number(((barWidth / metrics.width) * 100).toFixed(2)),
                            incomeValue: point.income,
                            incomeX: incomeRect.x,
                            incomeY: incomeRect.y,
                            incomeHeight: incomeRect.height,
                            incomeCenterPct: Number((((incomeRect.x + (barWidth / 2)) / metrics.width) * 100).toFixed(2)),
                            incomeLeftPct: Number(((incomeRect.x / metrics.width) * 100).toFixed(2)),
                            incomeTopPct: Number(((incomeRect.y / metrics.height) * 100).toFixed(2)),
                            incomeHeightPct: Number(((incomeRect.height / metrics.height) * 100).toFixed(2)),
                            incomeLabelTopPct: Number((((Math.max(10, incomeRect.y - 28)) / metrics.height) * 100).toFixed(2)),
                            spendingValue: point.spending,
                            spendingX: spendingRect.x,
                            spendingY: spendingRect.y,
                            spendingHeight: spendingRect.height,
                            spendingCenterPct: Number((((spendingRect.x + (barWidth / 2)) / metrics.width) * 100).toFixed(2)),
                            spendingLeftPct: Number(((spendingRect.x / metrics.width) * 100).toFixed(2)),
                            spendingTopPct: Number(((spendingRect.y / metrics.height) * 100).toFixed(2)),
                            spendingHeightPct: Number(((spendingRect.height / metrics.height) * 100).toFixed(2)),
                            spendingLabelTopPct: Number((((Math.max(10, spendingRect.y - 28)) / metrics.height) * 100).toFixed(2)),
                            openingX: Number((centerX - (columnWidth * 0.28)).toFixed(2)),
                            peakX: Number((incomeRect.x + (barWidth / 2)).toFixed(2)),
                            afterBillsX: Number((centerX + (columnWidth * 0.02)).toFixed(2)),
                            endingX: Number((centerX + (columnWidth * 0.28)).toFixed(2)),
                        };
                    });
                },
                get carryBalancePoints() {
                    const metrics = this.chartMetrics();

                    return this.chartDataPoints.map((point, index) => {
                        const column = this.chartColumns[index];
                        const y = this.chartY(point.openingBalance);

                        return {
                            ...point,
                            x: column?.openingX ?? 0,
                            y,
                            xPct: Number((((column?.openingX ?? 0) / metrics.width) * 100).toFixed(2)),
                            yPct: Number(((y / metrics.height) * 100).toFixed(2)),
                        };
                    });
                },
                get salaryPeakPoints() {
                    const metrics = this.chartMetrics();

                    return this.chartDataPoints.map((point, index) => {
                        const column = this.chartColumns[index];
                        const y = this.chartY(point.startingBalance);

                        return {
                            ...point,
                            x: column?.peakX ?? 0,
                            y,
                            xPct: Number((((column?.peakX ?? 0) / metrics.width) * 100).toFixed(2)),
                            yPct: Number(((y / metrics.height) * 100).toFixed(2)),
                        };
                    });
                },
                get afterBillsBalancePoints() {
                    const metrics = this.chartMetrics();

                    return this.chartDataPoints.map((point, index) => {
                        if (point.balanceAfterBills === null) {
                            return null;
                        }

                        return {
                            ...point,
                            x: this.chartColumns[index]?.afterBillsX ?? 0,
                            y: this.chartY(point.balanceAfterBills),
                            xPct: Number((((this.chartColumns[index]?.afterBillsX ?? 0) / metrics.width) * 100).toFixed(2)),
                            yPct: Number(((this.chartY(point.balanceAfterBills) / metrics.height) * 100).toFixed(2)),
                        };
                    });
                },
                get endingBalancePoints() {
                    const metrics = this.chartMetrics();

                    return this.chartDataPoints.map((point, index) => ({
                        ...point,
                        x: this.chartColumns[index]?.endingX ?? 0,
                        y: this.chartY(point.endingBalance),
                        xPct: Number((((this.chartColumns[index]?.endingX ?? 0) / metrics.width) * 100).toFixed(2)),
                        yPct: Number(((this.chartY(point.endingBalance) / metrics.height) * 100).toFixed(2)),
                        labelTopPct: Number((((Math.max(14, this.chartY(point.endingBalance) - (point.endingBalance >= 0 ? 34 : -10))) / metrics.height) * 100).toFixed(2)),
                    }));
                },
                get balanceFlowPoints() {
                    const points = [];

                    this.chartDataPoints.forEach((point, index) => {
                        const carry = this.carryBalancePoints[index];
                        const peak = this.salaryPeakPoints[index];
                        const afterBills = this.afterBillsBalancePoints[index];
                        const ending = this.endingBalancePoints[index];

                        if (carry) {
                            points.push({
                                label: `${point.label}-carry`,
                                x: carry.x,
                                y: carry.y,
                            });
                        }

                        if (peak && point.income > 0) {
                            points.push({
                                label: `${point.label}-peak`,
                                x: peak.x,
                                y: peak.y,
                            });
                        }

                        if (afterBills) {
                            points.push({
                                label: `${point.label}-after-bills`,
                                x: afterBills.x,
                                y: afterBills.y,
                            });
                        }

                        if (ending) {
                            points.push({
                                label: `${point.label}-ending`,
                                x: ending.x,
                                y: ending.y,
                            });
                        }
                    });

                    return points;
                },
                get endingBalanceLinePath() {
                    return this.buildLinePath(this.balanceFlowPoints);
                },
            };
        }
    </script>
</x-app-layout>
