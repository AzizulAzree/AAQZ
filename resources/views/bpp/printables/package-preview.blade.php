<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm sm:rounded-2xl">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="project-tree-label">{{ __('BPP Preview') }}</p>
                            <h1 class="mt-2 text-xl font-semibold text-slate-900">{{ $bpp->title }}</h1>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('bpp.show', $bpp) }}" class="bpp-secondary-button">{{ __('Back To Draft') }}</a>
                            <a href="{{ route('bpp.pdf', $bpp) }}" class="bpp-primary-button" target="_blank" rel="noopener noreferrer">{{ __('Generate PDF') }}</a>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-6">
                @include('bpp.printables.a4-blank')
            </div>
        </div>
    </div>
</x-app-layout>
