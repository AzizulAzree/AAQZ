<section id="bpp-overview" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('overview')" :aria-expanded="isOpen('overview')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Draft Overview') }}</p>
            <h2 class="workspace-section-title">{{ $bpp->title }}</h2>
            <p class="workspace-section-copy">{{ __('Draft summary and actions.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('overview') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('overview')">
        <div class="workspace-section-body">
            @if ($workspaceStatusMessage !== null)
                <div class="{{ $workspaceStatusMessage['classes'] }}">
                    {{ $workspaceStatusMessage['message'] }}
                </div>
            @endif

            <div class="workspace-overview-grid">
                <div class="workspace-overview-primary">
                    <div class="workspace-readiness-summary">
                        <div @class([
                            'rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]',
                            'bg-rose-100 text-rose-700' => $validationResult['state']['tone'] === 'rose',
                            'bg-amber-100 text-amber-800' => $validationResult['state']['tone'] === 'amber',
                            'bg-emerald-100 text-emerald-700' => $validationResult['state']['tone'] === 'emerald',
                        ])>
                            {{ __($validationResult['state']['label']) }}
                        </div>
                        <p class="text-sm text-slate-600">{{ __($validationResult['state']['message']) }}</p>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-sm text-slate-600">
                            <p>{{ __('This draft will be used to complete the BPP form.') }}</p>
                            <p class="mt-2">{{ __('Future BPP sections will be attached to this record.') }}</p>
                        </div>
                    </div>

                    <div class="workspace-action-row">
                        <button type="submit" form="bpp-request-basics-form" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                            {{ __('Save Draft') }}
                        </button>
                        <a href="{{ route('bpp.export.pdf', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                            {{ __('Export PDF Package') }}
                        </a>
                        <a href="#bpp-output" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                            {{ __('Printable Preview') }}
                        </a>
                    </div>
                </div>

                <div class="workspace-overview-meta">
                    <div class="project-ghost-action">{{ __('Status') }}: {{ $bpp->status }}</div>
                    <div class="project-ghost-action">{{ __('ID') }}: {{ $bpp->id }}</div>
                    <div class="project-ghost-action">{{ __('Created') }}: {{ $bpp->created_at?->format('Y-m-d H:i') }}</div>
                    <div class="project-ghost-action">{{ __('Updated') }}: {{ $bpp->updated_at?->format('Y-m-d H:i') }}</div>
                    <div class="project-ghost-action">{{ __('Passed Checks') }}: {{ $validationResult['counts']['passed'] }}</div>
                    <div class="project-ghost-action">{{ __('Open Checks') }}: {{ $validationResult['counts']['warnings'] + $validationResult['counts']['blocking_issues'] }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
