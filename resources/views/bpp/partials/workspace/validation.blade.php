<section id="bpp-validation" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('validation')" :aria-expanded="isOpen('validation')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Validation / Readiness') }}</p>
            <h2 class="workspace-section-title">{{ __('Draft Readiness') }}</h2>
            <p class="workspace-section-copy">{{ __('Current validation status.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('validation') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('validation')">
        <div class="workspace-section-body">
            <div class="flex items-start justify-between gap-4">
                <div @class([
                    'rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]',
                    'bg-rose-100 text-rose-700' => $validationResult['state']['tone'] === 'rose',
                    'bg-amber-100 text-amber-800' => $validationResult['state']['tone'] === 'amber',
                    'bg-emerald-100 text-emerald-700' => $validationResult['state']['tone'] === 'emerald',
                ])>
                    {{ __($validationResult['state']['label']) }}
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                <p class="text-sm text-slate-600">{{ __($validationResult['state']['message']) }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ __('Passed Checks') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-800">{{ $validationResult['counts']['passed'] }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-800">{{ __('Warnings') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-amber-900">{{ $validationResult['counts']['warnings'] }}</p>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">{{ __('Blocking Issues') }}</p>
                        <p class="mt-2 text-2xl font-semibold text-rose-800">{{ $validationResult['counts']['blocking_issues'] }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5">
                    <p class="project-tree-label text-emerald-700">{{ __('Passed Checks') }}</p>
                    <div class="mt-4 space-y-3 text-sm text-emerald-800">
                        @forelse ($validationResult['passed'] as $check)
                            <div class="rounded-xl border border-emerald-200 bg-white/80 px-4 py-3">
                                <p class="font-semibold">{{ $check['code'] }}</p>
                                <p class="mt-1 text-emerald-700">{{ $check['message'] }}</p>
                            </div>
                        @empty
                            <p>{{ __('No passed checks yet.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5">
                    <p class="project-tree-label text-amber-800">{{ __('Warnings') }}</p>
                    <div class="mt-4 space-y-3 text-sm text-amber-900">
                        @forelse ($validationResult['warnings'] as $warning)
                            <div class="rounded-xl border border-amber-200 bg-white/80 px-4 py-3">
                                <p class="font-semibold">{{ $warning['code'] }}</p>
                                <p class="mt-1 text-amber-800">{{ $warning['message'] }}</p>
                            </div>
                        @empty
                            <p>{{ __('No warnings right now.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl border border-rose-200 bg-rose-50/70 p-5">
                    <p class="project-tree-label text-rose-700">{{ __('Blocking Issues') }}</p>
                    <div class="mt-4 space-y-3 text-sm text-rose-800">
                        @forelse ($validationResult['blocking_issues'] as $issue)
                            <div class="rounded-xl border border-rose-200 bg-white/80 px-4 py-3">
                                <p class="font-semibold">{{ $issue['code'] }}</p>
                                <p class="mt-1 text-rose-700">{{ $issue['message'] }}</p>
                            </div>
                        @empty
                            <p>{{ __('No blocking issues right now.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
