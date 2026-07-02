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
                                        <span class="text-sm text-slate-500">{{ __('Current Month Salary') }}</span>
                                        <span class="text-base font-semibold text-[#5A8DEE]" x-text="currency(currentSalary)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Current Month Commitment') }}</span>
                                        <span class="text-sm font-semibold text-rose-500" x-text="currency(-currentCommittedSpending)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Salary Balance Left After Bills') }}</span>
                                        <span class="text-sm font-semibold" :class="plannedRemaining >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="currency(plannedRemaining)"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-slate-500">{{ __('Balance Before Salary') }}</span>
                                        <span class="text-sm font-semibold text-slate-900" x-text="currency(carryBalance)"></span>
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <div class="space-y-6">
                            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-5 shadow-[0_12px_28px_rgba(15,23,42,0.05)]">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#87A1CC]">{{ __('Chart') }}</p>
                                        <h4 class="mt-2 text-xl font-semibold text-slate-950">{{ __('Income, bills, and balance') }}</h4>
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
                                            {{ __('Spending') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <div class="grid grid-cols-[4.25rem_minmax(0,1fr)] gap-4">
                                        <div class="flex h-[19rem] flex-col justify-between pb-10 text-sm font-medium text-[#8AA0C8]">
                                            <template x-for="mark in combinedAxisMarks" :key="mark">
                                                <span x-text="currency(mark)"></span>
                                            </template>
                                        </div>

                                        <div>
                                            <div class="relative h-[19rem] overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white px-4 pt-6">
                                                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_bottom,rgba(138,160,200,0.16)_1px,transparent_1px)] bg-[length:100%_25%]"></div>

                                                <svg viewBox="0 0 960 288" class="absolute inset-0 z-10 h-full w-full">
                                                    <defs>
                                                        <linearGradient id="finance-balance-fill" x1="0" y1="0" x2="0" y2="1">
                                                            <stop offset="0%" stop-color="#2FD39B" stop-opacity="0.18"></stop>
                                                            <stop offset="100%" stop-color="#2FD39B" stop-opacity="0.02"></stop>
                                                        </linearGradient>
                                                    </defs>

                                                    <path :d="balanceAreaPath" fill="url(#finance-balance-fill)"></path>
                                                </svg>

                                                <div class="absolute inset-x-9 bottom-7 top-[1.125rem] z-20 grid items-end gap-5" :style="`grid-template-columns: repeat(${activePreset.labels.length}, minmax(0, 1fr))`">
                                                    <template x-for="bar in combinedBarGroups" :key="'combined-html-bar-' + bar.label">
                                                        <div class="flex h-full items-end justify-center gap-3">
                                                            <div
                                                                class="rounded-t-[1rem] shadow-[0_14px_28px_rgba(90,141,238,0.18)]"
                                                                :style="`width:${bar.barWidthPx}px; height:${bar.incomeHeightPx}px; background: rgba(90,141,238,0.9); border: 1px solid rgba(90,141,238,0.2);`"
                                                            ></div>
                                                            <div
                                                                class="rounded-t-[1rem] shadow-[0_14px_28px_rgba(255,79,112,0.16)]"
                                                                :style="`width:${bar.barWidthPx}px; height:${bar.spendingHeightPx}px; background: rgba(255,79,112,0.86); border: 1px solid rgba(255,79,112,0.2);`"
                                                            ></div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <svg viewBox="0 0 960 288" class="relative z-30 h-full w-full">

                                                    <path
                                                        :d="balanceSplinePath"
                                                        fill="none"
                                                        stroke="#2FD39B"
                                                        stroke-width="5"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    ></path>

                                                    <template x-for="point in balancePoints" :key="'balance-' + point.label">
                                                        <g>
                                                            <circle :cx="point.x" :cy="point.y" r="6" fill="white" stroke="#2FD39B" stroke-width="4"></circle>
                                                        </g>
                                                    </template>
                                                </svg>
                                            </div>

                                            <div class="mt-5 grid gap-2 text-center text-sm font-semibold text-[#8AA0C8]" :style="`grid-template-columns: repeat(${activePreset.labels.length}, minmax(0, 1fr))`">
                                                <template x-for="label in activePreset.labels" :key="'balance-label-' + label">
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
                                    <select
                                        x-model="selectedMonthId"
                                        class="w-full rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-slate-400 focus:ring-slate-400 sm:w-auto"
                                    >
                                        <template x-for="month in monthStatuses" :key="month.id">
                                            <option :value="month.id" x-text="month.label"></option>
                                        </template>
                                    </select>
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
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonth.salary_date"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Paid So Far') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-emerald-600" x-text="currency(-selectedMonthPaidTotal)"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthPaidCount + ' commitments paid'"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Not Paid Yet') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-amber-600" x-text="currency(-selectedMonthUnpaidTotal)"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="selectedMonthUnpaidCount + ' commitments pending'"></p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Previous Month') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-950" x-text="previousMonth ? previousMonth.label : 'None'"></p>
                                    <p class="mt-1 text-sm text-slate-500" x-text="previousMonth ? currency(previousMonthPendingTotal) + ' pending comparison' : 'No previous month selected'"></p>
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
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('Total Spending Last Month') }}</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="previousMonth ? currency(-previousMonthTotalSpending) : 'RM0'"></p>
                                        <p class="mt-1 text-sm text-slate-500">{{ __('Salary balance minus end balance.') }}</p>
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
                get currentSalary() {
                    return this.activePreset.income[this.activePreset.income.length - 1] ?? 0;
                },
                get carryBalance() {
                    return this.activePreset.carry?.[this.activePreset.carry.length - 1] ?? 0;
                },
                get currentBalance() {
                    return this.activePreset.balance[this.activePreset.balance.length - 1] ?? 0;
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
                get previousMonthTotalSpending() {
                    if (!this.previousMonth) {
                        return 0;
                    }

                    const previousMonthStartingBalance = (this.previousMonth.salary ?? 0) + (this.previousMonth.carry_balance ?? 0);
                    const previousMonthEndBalance = this.selectedMonth?.carry_balance ?? 0;

                    return Math.max(0, previousMonthStartingBalance - previousMonthEndBalance);
                },
                get plannedRemaining() {
                    return this.currentBalance - this.currentCommittedSpending;
                },
                get netFlow() {
                    return this.plannedRemaining;
                },
                get summaryText() {
                    const gap = this.plannedRemaining;
                    const status = gap >= 0 ? 'should leave' : 'would go short by';

                    return `Carry-over before salary is ${this.currency(this.carryBalance)}. After adding this cycle salary of ${this.currency(this.currentSalary)}, current balance becomes ${this.currency(this.currentBalance)}. Planned commitments of ${this.currency(-this.currentCommittedSpending)} ${status} ${this.currency(Math.abs(gap))}.`;
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
                        balance: this.activePreset.balance[index] ?? 0,
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
                    this.carryForm.value = this.carryBalance;
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
                        const lastIndex = preset.balance.length - 1;

                        if (type === 'carry') {
                            preset.carry[lastIndex] = amount;
                            preset.balance[lastIndex] = amount + (preset.income[lastIndex] ?? 0);
                            return;
                        }

                        if (type === 'income') {
                            preset.income[lastIndex] = (preset.income[lastIndex] ?? 0) + amount;
                            preset.balance[lastIndex] = (preset.carry[lastIndex] ?? 0) + (preset.income[lastIndex] ?? 0);
                            return;
                        }

                        preset.spending[lastIndex] = (preset.spending[lastIndex] ?? 0) + amount;
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
                        height: 288,
                        left: 36,
                        right: 924,
                        top: 18,
                        bottom: 260,
                    };
                },
                buildBalancePoints() {
                    const values = this.activePreset.balance;
                    const metrics = this.chartMetrics();
                    const maxValue = this.combinedChartMax;
                    const plotWidth = metrics.right - metrics.left;
                    const plotHeight = metrics.bottom - metrics.top;
                    const stepX = values.length > 1 ? plotWidth / (values.length - 1) : plotWidth;

                    return values.map((value, index) => ({
                        label: this.activePreset.labels[index] ?? `Point ${index + 1}`,
                        value,
                        x: Number((metrics.left + (index * stepX)).toFixed(2)),
                        y: Number((metrics.bottom - ((value / maxValue) * plotHeight)).toFixed(2)),
                    }));
                },
                buildSplinePath(points) {
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
                        const controlX = (current.x + next.x) / 2;

                        path += ` C ${controlX} ${current.y}, ${controlX} ${next.y}, ${next.x} ${next.y}`;
                    }

                    return path;
                },
                buildAreaPath(points) {
                    if (!points.length) {
                        return '';
                    }

                    const baseline = this.chartMetrics().bottom;
                    return `${this.buildSplinePath(points)} L ${points[points.length - 1].x} ${baseline} L ${points[0].x} ${baseline} Z`;
                },
                get balancePoints() {
                    return this.buildBalancePoints();
                },
                get balanceSplinePath() {
                    return this.buildSplinePath(this.balancePoints);
                },
                get balanceAreaPath() {
                    return this.buildAreaPath(this.balancePoints);
                },
                get balanceAxisMarks() {
                    const ceiling = Math.max(...this.activePreset.balance);
                    const rounded = Math.ceil(ceiling / 400) * 400;

                    return [rounded, Math.round(rounded * 0.66), Math.round(rounded * 0.33), 0];
                },
                get combinedChartMax() {
                    const ceiling = Math.max(...this.activePreset.balance, ...this.activePreset.income, ...this.activePreset.spending);
                    return Math.ceil((ceiling * 1.15) / 250) * 250;
                },
                get combinedAxisMarks() {
                    const rounded = this.combinedChartMax;

                    return [rounded, Math.round(rounded * 0.66), Math.round(rounded * 0.33), 0];
                },
                get combinedBarGroups() {
                    const metrics = this.chartMetrics();
                    const plotHeight = metrics.bottom - metrics.top;
                    const groupWidth = (metrics.right - metrics.left) / Math.max(this.activePreset.labels.length, 1);
                    const barWidth = Math.max(18, Math.min(34, groupWidth * 0.24));
                    const barMax = this.combinedChartMax || 1;

                    return this.activePreset.labels.map((label, index) => {
                        const income = this.activePreset.income[index] ?? 0;
                        const spending = this.activePreset.spending[index] ?? 0;
                        const incomeHeight = Math.max(16, (income / barMax) * plotHeight);
                        const spendingHeight = Math.max(16, (spending / barMax) * plotHeight);

                        return {
                            label,
                            income,
                            spending,
                            barWidthPx: Number(barWidth.toFixed(2)),
                            incomeHeightPx: Number(incomeHeight.toFixed(2)),
                            spendingHeightPx: Number(spendingHeight.toFixed(2)),
                        };
                    });
                },
            };
        }
    </script>
</x-app-layout>
