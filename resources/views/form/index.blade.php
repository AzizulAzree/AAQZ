<x-app-layout>
    <div
        x-data="{
            shareUrl: @js($shareUrl),
            copied: false,
            customizeModalOpen: false,
            submissionViewOpen: false,
            activeSubmission: null,
            submissions: @js($submissions),
            formRows: @js($formRows),
            async copyUrl() {
                try {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(this.shareUrl);
                    } else {
                        const field = this.$refs.shareField;
                        field.focus();
                        field.select();
                        document.execCommand('copy');
                    }

                    this.copied = true;
                    window.clearTimeout(this.copyTimer);
                    this.copyTimer = window.setTimeout(() => this.copied = false, 2200);
                } catch (error) {
                    this.copied = false;
                }
            },
            openCustomizeModal() {
                this.customizeModalOpen = true;
            },
            addFormRow() {
                this.formRows.push({
                    id: Date.now() + this.formRows.length,
                    title: '',
                    type: 'text',
                    options_text: '',
                });
            },
            removeFormRow(index) {
                if (this.formRows.length <= 1) {
                    return;
                }

                this.formRows.splice(index, 1);
            },
            isChoiceType(type) {
                return ['dropdown', 'radio button', 'checkbox'].includes(type);
            },
            openSubmissionView(index) {
                this.activeSubmission = this.submissions[index] ?? null;
                this.submissionViewOpen = true;
            },
        }"
        class="py-12"
    >
        <form method="POST" action="{{ route('forms.store') }}" x-ref="saveForm" class="hidden">
            @csrf
            <input type="hidden" name="rows" x-bind:value="JSON.stringify(formRows)">
        </form>

        <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm sm:rounded-2xl">
                <div class="project-shell">
                    <div class="project-shell-copy">
                        <p class="project-shell-kicker">{{ __('Form') }}</p>
                        <h1 class="project-shell-title">{{ __('Ordering Smart Form') }}</h1>
                        <p class="project-shell-description">
                            {{ __('Share your personal form link with customers so they can reach your future ordering flow from one trusted URL.') }}
                        </p>
                    </div>

                    <div class="project-shell-actions">
                        <button type="button" class="project-primary-action" x-on:click="openCustomizeModal()">
                            {{ __('Customize Form') }}
                        </button>
                        <button
                            type="button"
                            class="project-ghost-action"
                            aria-label="{{ __('Open customize form help') }}"
                            title="{{ __('Open customize form help') }}"
                            x-on:click="openCustomizeModal()"
                        >
                            <span aria-hidden="true">&#9881;</span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-slate-200/80 bg-slate-50/70 px-5 py-5 sm:px-6">
                    @if (session('status') === 'form-saved')
                        <p class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ __('Ordering Smart Form saved.') }}
                        </p>
                    @elseif (session('status') === 'form-storage-unavailable')
                        <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            {{ __('Form storage is not ready yet. Run `php artisan migrate` to enable saving.') }}
                        </p>
                    @endif

                    <div class="grid gap-4">
                        <section class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                            <p class="project-tree-label">{{ __('Your unique customer URL') }}</p>
                            <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ __('Ready to share') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ __('This link is unique to your account. Customers can open it directly when you are ready to publish the full ordering form flow.') }}
                            </p>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <input
                                    x-ref="shareField"
                                    type="text"
                                    readonly
                                    value="{{ $shareUrl }}"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                >
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    x-on:click="copyUrl()"
                                >
                                    <span x-show="!copied">{{ __('Copy') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('Copied') }}</span>
                                </button>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
                            <p class="project-tree-label">{{ __('Form submissions') }}</p>
                            <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ __('Form Status') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ __('Inserted customer form data will appear here once the public ordering form starts receiving responses.') }}
                            </p>

                            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                            <tr>
                                                <th class="px-4 py-3">{{ __('Submitted At') }}</th>
                                                <th class="px-4 py-3">{{ __('Day submitted') }}</th>
                                                <th class="px-4 py-3">{{ __('Customer') }}</th>
                                                <th class="px-4 py-3">{{ __('View') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white text-slate-600">
                                            @forelse ($submissions as $submissionIndex => $submission)
                                                <tr>
                                                    <td class="px-4 py-4">{{ $submission['submitted_at']?->format('Y-m-d H:i') }}</td>
                                                    <td class="px-4 py-4">{{ $submission['day_submitted'] }}</td>
                                                    <td class="px-4 py-4 font-medium text-slate-700">{{ $submission['customer'] }}</td>
                                                    <td class="px-4 py-4">
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-700 transition hover:bg-slate-50"
                                                            x-on:click="openSubmissionView({{ $submissionIndex }})"
                                                        >
                                                            {{ __('View') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                                                        {{ __('No form submissions yet.') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>

        <template x-if="submissionViewOpen && activeSubmission">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="submissionViewOpen = false; activeSubmission = null"></div>
                <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="w-full border-b border-slate-200 bg-gradient-to-br from-slate-50 via-white to-amber-50/40 px-6 py-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">{{ __('Submitted entry') }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900" x-text="activeSubmission.customer"></h2>
                                    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 font-medium text-slate-600" x-text="activeSubmission.submitted_at_display"></span>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 font-medium text-emerald-700" x-text="activeSubmission.day_submitted"></span>
                                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 font-medium text-amber-700">
                                            <span x-text="activeSubmission.answer_count"></span>
                                            <span>{{ __(' responses') }}</span>
                                        </span>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                                    x-on:click="submissionViewOpen = false; activeSubmission = null"
                                >
                                    {{ __('Close') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[68vh] overflow-y-auto bg-slate-50/60 px-6 py-6">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Customer responses') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Review each answer exactly as submitted by the customer.') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                        <template x-for="(answer, answerIndex) in activeSubmission.answers" :key="answerIndex">
                            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <p class="text-sm font-semibold text-slate-800" x-text="answer.title"></p>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500" x-text="'#' + (answerIndex + 1)"></span>
                                </div>
                                <p class="mt-3 text-[0.95rem] leading-7 text-slate-600 whitespace-pre-wrap break-words" x-text="answer.value || '{{ __('No response') }}'"></p>
                            </div>
                        </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="customizeModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="customizeModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('Customize form') }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Customize Ordering Smart Form') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ __('This space is ready for your future form structure settings.') }}
                            </p>
                        </div>
                        <button type="button" class="project-modal-close" x-on:click="customizeModalOpen = false">
                            {{ __('Close') }}
                        </button>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 px-5 py-5">
                        <div>
                            <p class="project-tree-label">{{ __('Form builder') }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ __('Add the fields you want customers to see in the ordering form.') }}
                            </p>
                        </div>

                        <div class="mt-5 space-y-3">
                            <template x-for="(row, index) in formRows" :key="row.id">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0 flex-1">
                                            <label class="sr-only" x-bind:for="'form-row-title-' + row.id">
                                                {{ __('Title') }}
                                            </label>
                                            <input
                                                x-bind:id="'form-row-title-' + row.id"
                                                x-model="row.title"
                                                type="text"
                                                placeholder="{{ __('Title') }}"
                                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                            >
                                        </div>

                                        <div class="w-52 shrink-0">
                                            <label class="sr-only" x-bind:for="'form-row-type-' + row.id">
                                                {{ __('Type') }}
                                            </label>
                                            <select
                                                x-bind:id="'form-row-type-' + row.id"
                                                x-model="row.type"
                                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                            >
                                                <option value="date">{{ __('date') }}</option>
                                                <option value="text">{{ __('text') }}</option>
                                                <option value="money">{{ __('money') }}</option>
                                                <option value="number">{{ __('number') }}</option>
                                                <option value="dropdown">{{ __('dropdown') }}</option>
                                                <option value="radio button">{{ __('radio button') }}</option>
                                                <option value="checkbox">{{ __('checkbox') }}</option>
                                            </select>
                                        </div>

                                        <div class="shrink-0">
                                            <template x-if="index === formRows.length - 1">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-11 w-16 items-center justify-center rounded-xl border border-slate-300 bg-white text-lg font-semibold text-slate-700 transition hover:bg-slate-50"
                                                    x-on:click="addFormRow()"
                                                    aria-label="{{ __('Add another row') }}"
                                                    title="{{ __('Add another row') }}"
                                                >
                                                    +
                                                </button>
                                            </template>

                                            <template x-if="index !== formRows.length - 1">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-11 w-16 items-center justify-center rounded-xl border border-slate-300 bg-white text-lg font-semibold text-slate-700 transition hover:bg-slate-50"
                                                    x-on:click="removeFormRow(index)"
                                                    aria-label="{{ __('Remove this row') }}"
                                                    title="{{ __('Remove this row') }}"
                                                >
                                                    -
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="isChoiceType(row.type)" x-cloak class="mt-3">
                                        <label class="sr-only" x-bind:for="'form-row-options-' + row.id">
                                            {{ __('Options') }}
                                        </label>
                                        <textarea
                                            x-bind:id="'form-row-options-' + row.id"
                                            x-model="row.options_text"
                                            rows="4"
                                            placeholder="{{ __('Options (one per line)') }}"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-300 focus:ring-slate-300"
                                        ></textarea>
                                        <p class="mt-2 text-xs text-slate-500">
                                            {{ __('Enter one option per line.') }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50"
                            x-on:click="$refs.saveForm.submit()"
                        >
                            {{ __('Done') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
