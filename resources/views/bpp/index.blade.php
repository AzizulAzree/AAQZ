<x-app-layout>
    <div
        x-data="{ createBppModalOpen: false }"
        x-init="
            @if ($errors->has('title') || $errors->has('b2_kategori_perolehan'))
                createBppModalOpen = true;
            @endif
        "
        class="py-12"
    >
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm sm:rounded-2xl">
                <div class="project-shell">
                    <div class="project-shell-copy">
                        <p class="project-shell-kicker">{{ __('BPP') }}</p>
                        <h1 class="project-shell-title">{{ __('BPP (Borang Permohonan Perolehan)') }}</h1>
                        <p class="project-shell-description">
                            {{ __('Start each procurement request as a draft record before completing the full BPP process.') }}
                        </p>
                    </div>

                    <div class="project-shell-actions">
                        <button
                            type="button"
                            class="project-primary-action"
                            x-on:click="createBppModalOpen = true"
                        >
                            {{ __('Start New BPP') }}
                        </button>
                        <div class="project-ghost-action">
                            {{ trans_choice('{1} :count draft|[2,*] :count drafts', $bpps->count(), ['count' => $bpps->count()]) }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200/80 bg-slate-50/70 px-5 py-5 sm:px-6">
                    @if ($bpps->isEmpty())
                        <div class="project-empty-state">
                            <h2 class="project-empty-title">{{ __('No BPP drafts yet') }}</h2>
                            <p class="project-empty-copy">{{ __('Create your first BPP draft to begin the procurement request flow.') }}</p>
                            <button type="button" class="project-primary-action" x-on:click="createBppModalOpen = true">
                                {{ __('Start New BPP') }}
                            </button>
                        </div>
                    @else
                        <div class="grid gap-4">
                            @foreach ($bpps as $bpp)
                                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 transition hover:border-slate-300 hover:shadow-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="project-tree-label">{{ __('BPP Draft') }}</p>
                                            <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $bpp->title }}</h2>
                                            <div class="mt-3 space-y-1 text-sm text-slate-500">
                                                <p>{{ __('ID') }}: {{ $bpp->id }}</p>
                                                <p>{{ __('Updated') }}: {{ $bpp->updated_at?->format('Y-m-d H:i') }}</p>
                                                <p>{{ __('Created') }}: {{ $bpp->created_at?->format('Y-m-d H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-3">
                                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">
                                                {{ $bpp->status }}
                                            </span>
                                            <a
                                                href="{{ route('bpp.show', $bpp) }}"
                                                class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50"
                                            >
                                                {{ __('Continue Draft') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <template x-if="createBppModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-slate-900/40" x-on:click="createBppModalOpen = false"></div>
                <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">{{ __('New BPP') }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ __('Start New BPP') }}</h2>
                        </div>
                        <button type="button" class="project-modal-close" x-on:click="createBppModalOpen = false">{{ __('Cancel') }}</button>
                    </div>

                    <form method="POST" action="{{ route('bpp.store') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="bpp-title" :value="__('Title')" />
                            <x-text-input
                                id="bpp-title"
                                name="title"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('title')"
                                required
                                autofocus
                                maxlength="255"
                                autocomplete="off"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="bpp-kategori-perolehan" :value="__('B2. Kategori Perolehan')" />
                            <select
                                id="bpp-kategori-perolehan"
                                name="b2_kategori_perolehan"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                                required
                            >
                                <option value="">{{ __('Select kategori perolehan') }}</option>
                                <option value="Bekalan" @selected(old('b2_kategori_perolehan') === 'Bekalan')>{{ __('Bekalan') }}</option>
                                <option value="Perkhidmatan" @selected(old('b2_kategori_perolehan') === 'Perkhidmatan')>{{ __('Perkhidmatan') }}</option>
                                <option value="Kerja" @selected(old('b2_kategori_perolehan') === 'Kerja')>{{ __('Kerja') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('b2_kategori_perolehan')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50"
                                x-on:click="createBppModalOpen = false"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <x-primary-button>{{ __('Create') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
