<section id="bpp-output" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('output')" :aria-expanded="isOpen('output')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Output') }}</p>
            <h2 class="workspace-section-title">{{ __('Preview And Export') }}</h2>
            <p class="workspace-section-copy">{{ __('Printable pages and final package export.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('output') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('output')">
        <div class="workspace-section-body">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 text-sm text-slate-600">
                <p>{{ __('Package: Senarai Semak, BPP Page 1, BPP Page 2, C1, and the active appendix only.') }}</p>
                <p class="mt-2">{{ __('Export uses the current saved draft.') }}</p>
            </div>
            <div class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <p class="project-tree-label">{{ __('Always Included') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('bpp.printables.checklist', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Open Senarai Semak') }}</a>
                        <a href="{{ route('bpp.printables.page-one', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Open BPP Page 1') }}</a>
                        <a href="{{ route('bpp.printables.page-two', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Open BPP Page 2') }}</a>
                        <a href="{{ route('bpp.printables.c1', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Open C1') }}</a>
                    </div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5">
                        <p class="project-tree-label text-amber-800">{{ __('Category Appendix') }}</p>
                    @if ($activeAppendixType !== null && $activeAppendixLabel !== null)
                        <p class="mt-3 text-lg font-semibold text-slate-900">{{ __($activeAppendixLabel) }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ __('This is the only appendix used in preview and export.') }}</p>
                        <div class="mt-4">
                            <a href="{{ route('bpp.printables.'.$activeAppendixType, $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-white">
                                {{ __('Open Active Appendix') }}
                            </a>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-600">{{ __('Choose kategori perolehan first.') }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="{{ route('bpp.export.pdf', $bpp) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">{{ __('Export PDF Package') }}</a>
            </div>
        </div>
    </div>
</section>
