<section class="bg-white shadow-sm sm:rounded-2xl print:hidden">
    <div class="p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="project-tree-label">{{ __('Printable Preview') }}</p>
                <h1 class="mt-2 text-xl font-semibold text-slate-900">{{ __($previewTitle) }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('Read-only printable preview for the current BPP draft state.') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bpp.show', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('Back To Draft') }}
                </a>
                <a href="{{ route('bpp.printables.preview', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('Preview BPP') }}
                </a>
                <a href="{{ route('bpp.printables.checklist', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('Senarai Semak') }}
                </a>
                <a href="{{ route('bpp.printables.page-one', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('BPP Page 1') }}
                </a>
                <a href="{{ route('bpp.printables.page-two', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('BPP Page 2') }}
                </a>
                <a href="{{ route('bpp.printables.c1', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('C1') }}
                </a>
                <a href="{{ route('bpp.printables.c2', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('C2') }}
                </a>
                <a href="{{ route('bpp.printables.c3', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('C3') }}
                </a>
                <a href="{{ route('bpp.printables.c4', $bpp) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                    {{ __('C4') }}
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                    {{ __('Print Preview') }}
                </button>
            </div>
        </div>
    </div>
</section>
