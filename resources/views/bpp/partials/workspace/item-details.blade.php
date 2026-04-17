<section id="bpp-item-details" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('items')" :aria-expanded="isOpen('items')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Item Details') }}</p>
            <h2 class="workspace-section-title">{{ $activeAppendixLabel ?? __('Active Appendix') }}</h2>
            <p class="workspace-section-copy">{{ __('Active appendix rows.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('items') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('items')">
        <div class="workspace-section-body">
            @if ($activeAppendixType === null)
                <p class="project-tree-label">{{ __('Appendix') }}</p>
                <p class="mt-3 text-sm text-slate-500">{{ __('Choose kategori perolehan first.') }}</p>
            @else
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="project-tree-label">{{ __('Appendix Editor') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __($activeAppendixLabel) }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Rows here update B3 automatically.') }}</p>
                    </div>
                    <div class="project-ghost-action">{{ __('Grand Total') }}: {{ $bpp->b3_nilai_tawaran_perolehan ?: '0.00' }}</div>
                </div>

                <div class="mt-8 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Line') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Item / Spesifikasi') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Kuantiti') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Unit') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Harga Seunit') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Jumlah Harga') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($activeAppendixRows as $row)
                                <tr x-data="{ editRowModalOpen: false }">
                                    <td class="px-3 py-4 align-top text-slate-500">{{ $row->line_number }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $row->item_spesifikasi }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ number_format((float) $row->kuantiti, 2, '.', '') }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $row->unit_ukuran }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ number_format((float) $row->harga_seunit, 2, '.', '') }}</td>
                                    <td class="px-3 py-4 align-top font-medium text-slate-700">{{ number_format((float) $row->jumlah_harga, 2, '.', '') }}</td>
                                    <td class="px-3 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50" x-on:click="editRowModalOpen = true">{{ __('Edit') }}</button>
                                            <form method="POST" action="{{ route('bpp.appendix-rows.destroy', [$bpp, $row]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md border border-rose-200 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-rose-700 transition hover:bg-rose-50">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                        <template x-if="editRowModalOpen">
                                            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                                <div class="absolute inset-0 bg-slate-900/40" x-on:click="editRowModalOpen = false"></div>
                                                <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <div>
                                                            <p class="text-sm text-slate-500">{{ __('Edit Appendix Row') }}</p>
                                                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __($activeAppendixLabel) }}</h3>
                                                        </div>
                                                        <button type="button" class="project-modal-close" x-on:click="editRowModalOpen = false">{{ __('Close') }}</button>
                                                    </div>

                                                    <form method="POST" action="{{ route('bpp.appendix-rows.update', [$bpp, $row]) }}" class="mt-6 grid gap-4 md:grid-cols-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="appendix_type" value="{{ $row->appendix_type }}">
                                                        <div class="md:col-span-2">
                                                            <x-input-label :for="'edit-item-'.$row->id" :value="__('Item / Spesifikasi')" />
                                                            <textarea :id="'edit-item-'.$row->id" name="item_spesifikasi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ $row->item_spesifikasi }}</textarea>
                                                        </div>
                                                        <div><x-input-label :for="'edit-kuantiti-'.$row->id" :value="__('Kuantiti')" /><x-text-input :id="'edit-kuantiti-'.$row->id" name="kuantiti" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="$row->kuantiti" /></div>
                                                        <div><x-input-label :for="'edit-unit-'.$row->id" :value="__('Unit Ukuran')" /><x-text-input :id="'edit-unit-'.$row->id" name="unit_ukuran" type="text" class="mt-1 block w-full" :value="$row->unit_ukuran" /></div>
                                                        <div><x-input-label :for="'edit-harga-'.$row->id" :value="__('Harga Seunit')" /><x-text-input :id="'edit-harga-'.$row->id" name="harga_seunit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$row->harga_seunit" /></div>
                                                        <div class="md:col-span-2 flex items-center gap-3 pt-2">
                                                            <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50" x-on:click="editRowModalOpen = false">{{ __('Cancel') }}</button>
                                                            <x-primary-button>{{ __('Save Row') }}</x-primary-button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">{{ __('No rows added yet for this appendix.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="project-tree-label">{{ __('Add Row') }}</p>
                    <form method="POST" action="{{ route('bpp.appendix-rows.store', $bpp) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" name="appendix_type" value="{{ $activeAppendixType }}">
                        <div class="md:col-span-2"><x-input-label for="new-item_spesifikasi" :value="__('Item / Spesifikasi')" /><textarea id="new-item_spesifikasi" name="item_spesifikasi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('item_spesifikasi') }}</textarea></div>
                        <div><x-input-label for="new-kuantiti" :value="__('Kuantiti')" /><x-text-input id="new-kuantiti" name="kuantiti" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('kuantiti')" /></div>
                        <div><x-input-label for="new-unit_ukuran" :value="__('Unit Ukuran')" /><x-text-input id="new-unit_ukuran" name="unit_ukuran" type="text" class="mt-1 block w-full" :value="old('unit_ukuran')" /></div>
                        <div><x-input-label for="new-harga_seunit" :value="__('Harga Seunit')" /><x-text-input id="new-harga_seunit" name="harga_seunit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('harga_seunit')" /></div>
                        <div class="md:col-span-2">
                            <x-input-error class="mt-2" :messages="$errors->get('appendix_type')" />
                            <x-input-error class="mt-2" :messages="$errors->get('item_spesifikasi')" />
                            <x-input-error class="mt-2" :messages="$errors->get('kuantiti')" />
                            <x-input-error class="mt-2" :messages="$errors->get('unit_ukuran')" />
                            <x-input-error class="mt-2" :messages="$errors->get('harga_seunit')" />
                        </div>
                        <div class="md:col-span-2"><button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">{{ __('Add Row') }}</button></div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</section>
