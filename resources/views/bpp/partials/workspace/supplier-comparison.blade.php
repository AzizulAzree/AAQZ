<section id="bpp-supplier-comparison" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl" x-data="{
    copiedPrompt: false,
    async copyPrompt() {
        const value = this.$refs.extractionPrompt?.value ?? '';

        if (window.navigator?.clipboard?.writeText) {
            await window.navigator.clipboard.writeText(value);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                if (! document.execCommand('copy')) {
                    throw new Error('Copy command was rejected.');
                }
            } finally {
                document.body.removeChild(textarea);
            }
        }

        this.copiedPrompt = true;
        setTimeout(() => this.copiedPrompt = false, 1800);
    }
}">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('supplier')" :aria-expanded="isOpen('supplier')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Supplier Comparison') }}</p>
            <h2 class="workspace-section-title">{{ __('C1 And Extraction') }}</h2>
            <p class="workspace-section-copy">{{ __('Supplier comparison and quotation intake.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('supplier') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('supplier')">
        <div class="workspace-section-body">
            @php
                $reviewErrors = is_array($quotationExtractionReview['errors'] ?? null) ? $quotationExtractionReview['errors'] : [];
                $reviewWarnings = is_array($quotationExtractionReview['warnings'] ?? null) ? $quotationExtractionReview['warnings'] : [];
                $reviewData = is_array($quotationExtractionReview['data'] ?? null) ? $quotationExtractionReview['data'] : null;
            @endphp

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <p class="project-tree-label">{{ __('Selected Supplier') }}</p>
                        <p class="mt-3 text-lg font-semibold text-slate-900">{{ $selectedSupplier?->supplier_name ?: __('No supplier selected yet') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $bpp->d_kriteria_pemilihan ?: __('No selection reason yet.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="project-tree-label">{{ __('Supplier Rows') }}</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $supplierQuotes->count() }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Saved C1 entries.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="project-tree-label">{{ __('Extraction Review') }}</p>
                    <p class="mt-3 text-lg font-semibold text-slate-900">{{ $reviewData !== null ? __('Ready To Review') : __('No Pending Review') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Paste and review extraction results here.') }}</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="project-tree-label">{{ __('Assistant') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Quotation Extraction Assistant') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Copy the prompt, paste the result, then review it here.') }}</p>
                    </div>
                    <div class="project-ghost-action">{{ $quotationExtractionFormatVersion }}</div>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="project-tree-label">{{ __('Workflow') }}</p>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p>{{ __('1. Copy the prepared extraction prompt.') }}</p>
                        <p>{{ __('2. Use it in ChatGPT with the quotation files.') }}</p>
                        <p>{{ __('3. Paste the returned structured block below.') }}</p>
                        <p>{{ __('4. Review the parsed result before you apply it.') }}</p>
                    </div>
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-100" x-on:click="copyPrompt().catch(() => {})">{{ __('Copy Prepared Prompt') }}</button>
                        <span class="text-xs text-slate-500" x-show="copiedPrompt" x-transition.opacity>{{ __('Prompt copied.') }}</span>
                    </div>
                    <textarea x-ref="extractionPrompt" class="sr-only">{{ $quotationExtractionPrompt }}</textarea>
                </div>

                <form method="POST" action="{{ route('bpp.quotation-extraction.parse', $bpp) }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                    @csrf
                    <div>
                        <x-input-label for="quotation_extraction_text" :value="__('Paste Structured Extraction Result')" />
                        <textarea id="quotation_extraction_text" name="quotation_extraction_text" rows="14" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('quotation_extraction_text', $bpp->quotation_extraction_raw_text) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('quotation_extraction_text')" />
                    </div>
                    <div class="mt-4"><button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">{{ __('Review Extraction') }}</button></div>
                </form>

                @if ($reviewErrors !== [])
                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                        <p class="project-tree-label text-rose-700">{{ __('Parse Errors') }}</p>
                        <div class="mt-3 space-y-2 text-sm text-rose-700">@foreach ($reviewErrors as $error)<p>{{ $error }}</p>@endforeach</div>
                    </div>
                @endif

                @if ($reviewData !== null)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="project-tree-label">{{ __('Review') }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ __('Parsed Extraction Review') }}</h3>
                                <p class="mt-2 text-sm text-slate-500">{{ __('Review before apply.') }}</p>
                            </div>
                            <div class="text-right text-sm text-slate-600">
                                <p>{{ __('Category') }}: {{ $reviewData['procurement_category'] ?? '-' }}</p>
                                <p>{{ __('Appendix') }}: {{ $reviewData['appendix_label'] ?? '-' }}</p>
                                <p>{{ __('Selected Supplier') }}: {{ $reviewData['selected_supplier'] ?? '-' }}</p>
                            </div>
                        </div>
                        @if ($reviewWarnings !== [])
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"><p class="font-semibold">{{ __('Warnings') }}</p><div class="mt-2 space-y-1">@foreach ($reviewWarnings as $warning)<p>{{ $warning }}</p>@endforeach</div></div>
                        @endif
                        @if ($hasExistingImportedDraftData)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"><p class="font-semibold">{{ __('Replace Existing Draft Data') }}</p><p class="mt-2">{{ __('Applying this extraction will replace the current C1 supplier comparison entries and appendix rows stored in this draft.') }}</p></div>
                        @endif
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="project-tree-label">{{ __('Selected Supplier Reason') }}</p><p class="mt-3 text-sm text-slate-700">{{ $reviewData['selection_reason'] ?? '-' }}</p>@if (! empty($reviewData['selection_reason_lain_lain'] ?? null))<p class="mt-2 text-sm text-slate-500">{{ $reviewData['selection_reason_lain_lain'] }}</p>@endif</div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="project-tree-label">{{ __('Totals') }}</p><p class="mt-3 text-sm text-slate-700">{{ __('Appendix Total') }}: {{ $reviewData['totals']['appendix_total'] ?? '-' }}</p><p class="mt-2 text-sm text-slate-700">{{ __('Selected Supplier Total') }}: {{ $reviewData['totals']['selected_supplier_total'] ?? '-' }}</p></div>
                        </div>
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Supplier Name') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Total Price') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Delivery Period') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Validity Period') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Quotation Reference') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Selected') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse (($reviewData['suppliers'] ?? []) as $supplier)
                                        <tr>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $supplier['supplier_name'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $supplier['total_price'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $supplier['delivery_period'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $supplier['validity_period'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $supplier['quotation_reference'] ?: '-' }}</td>
                                            <td class="px-3 py-4 align-top">@if ($supplier['is_selected'])<span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ __('Selected') }}</span>@else<span class="text-slate-400">-</span>@endif</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No supplier rows were available in this review snapshot.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Line') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Item / Spesifikasi') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Kuantiti') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Unit') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Harga Seunit') }}</th>
                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Jumlah Harga') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse (($reviewData['appendix_rows'] ?? []) as $row)
                                        <tr>
                                            <td class="px-3 py-4 align-top text-slate-500">{{ $row['line_number'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $row['item_spesifikasi'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $row['kuantiti'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $row['unit_ukuran'] }}</td>
                                            <td class="px-3 py-4 align-top text-slate-700">{{ $row['harga_seunit'] }}</td>
                                            <td class="px-3 py-4 align-top font-medium text-slate-700">{{ $row['jumlah_harga'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No appendix rows were available in this review snapshot.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <form method="POST" action="{{ route('bpp.quotation-extraction.apply', $bpp) }}" class="mt-6 space-y-4">
                            @csrf
                            @if ($hasExistingImportedDraftData)
                                <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" name="confirm_replace" value="1" class="mt-1 rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500"><span>{{ __('I understand this will replace the current C1 supplier comparison and appendix rows in this draft.') }}</span></label>
                                <x-input-error class="mt-2" :messages="$errors->get('confirm_replace')" />
                            @endif
                            <div><button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">{{ __('Apply Extraction To Draft') }}</button></div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="project-tree-label">{{ __('C1') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('C1 - Kajian Pasaran') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('Manual supplier comparison.') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('bpp.update', $bpp) }}" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $bpp->title }}">
                    <input type="hidden" name="no_rujukan_perolehan" value="{{ $bpp->no_rujukan_perolehan }}">
                    <input type="hidden" name="tajuk_dokumen" value="{{ $bpp->tajuk_dokumen }}">
                    <input type="hidden" name="ruj_dokumen" value="{{ $bpp->ruj_dokumen }}">
                    <input type="hidden" name="no_semakan" value="{{ $bpp->no_semakan }}">
                    <input type="hidden" name="tarikh_kuat_kuasa" value="{{ $bpp->tarikh_kuat_kuasa }}">
                    <input type="hidden" name="muka_surat" value="{{ $bpp->muka_surat }}">
                    <input type="hidden" name="a1_nama_pemohon" value="{{ $bpp->a1_nama_pemohon }}">
                    <input type="hidden" name="a2_jawatan_gred" value="{{ $bpp->a2_jawatan_gred }}">
                    <input type="hidden" name="a3_jabatan_institusi" value="{{ $bpp->a3_jabatan_institusi }}">
                    <input type="hidden" name="a4_no_tel_email" value="{{ $bpp->a4_no_tel_email }}">
                    <input type="hidden" name="kaedah_perolehan" value="{{ $bpp->kaedah_perolehan }}">
                    <input type="hidden" name="b1_tajuk_perolehan" value="{{ $bpp->b1_tajuk_perolehan }}">
                    <input type="hidden" name="b2_kategori_perolehan" value="{{ $bpp->b2_kategori_perolehan }}">
                    <input type="hidden" name="b3_nilai_tawaran_perolehan" value="{{ $bpp->b3_nilai_tawaran_perolehan }}">
                    <input type="hidden" name="b4_harga_indikatif" value="{{ $bpp->b4_harga_indikatif }}">
                    <input type="hidden" name="b5_peruntukan_diluluskan" value="{{ $bpp->b5_peruntukan_diluluskan }}">
                    <input type="hidden" name="b6_justifikasi_keperluan" value="{{ $bpp->b6_justifikasi_keperluan }}">
                    <input type="hidden" name="b7_tajuk_asal_perolehan" value="{{ $bpp->b7_tajuk_asal_perolehan }}">
                    <input type="hidden" name="b8_tarikh_diperlukan" value="{{ $bpp->b8_tarikh_diperlukan }}">
                    <input type="hidden" name="b9_lokasi_diperlukan" value="{{ $bpp->b9_lokasi_diperlukan }}">
                    <input type="hidden" name="d_nama_pembekal" value="{{ $bpp->d_nama_pembekal }}">
                    <input type="hidden" name="d_alamat_pembekal" value="{{ $bpp->d_alamat_pembekal }}">
                    <input type="hidden" name="d_no_pendaftaran_syarikat" value="{{ $bpp->d_no_pendaftaran_syarikat }}">
                    <input type="hidden" name="d_kriteria_pemilihan" value="{{ $bpp->d_kriteria_pemilihan }}">
                    <input type="hidden" name="d_lain_lain_kriteria" value="{{ $bpp->d_lain_lain_kriteria }}">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="c1_selection_reason" :value="__('Sebab Pemilihan Pembekal Disyorkan')" />
                            <select id="c1_selection_reason" name="c1_selection_reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                <option value="">{{ __('Select reason') }}</option>
                                @foreach ($selectionReasonOptions as $reasonOption)
                                    <option value="{{ $reasonOption }}" @selected(old('c1_selection_reason', $bpp->c1_selection_reason) === $reasonOption)>{{ $reasonOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><x-input-label for="c1_selection_reason_lain_lain" :value="__('Lain-lain')"/><textarea id="c1_selection_reason_lain_lain" name="c1_selection_reason_lain_lain" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('c1_selection_reason_lain_lain', $bpp->c1_selection_reason_lain_lain) }}</textarea></div>
                    </div>
                    <div class="mt-4"><button type="submit" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Save C1 Reason') }}</button></div>
                </form>

                <div class="mt-8 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Supplier Name') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Total Price') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Delivery Period') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Validity Period') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Quotation Reference') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Selected') }}</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($supplierQuotes as $supplierQuote)
                                <tr x-data="{ editQuoteModalOpen: false }">
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $supplierQuote->supplier_name }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ number_format((float) $supplierQuote->total_price, 2, '.', '') }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $supplierQuote->delivery_period }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $supplierQuote->validity_period }}</td>
                                    <td class="px-3 py-4 align-top text-slate-700">{{ $supplierQuote->quotation_reference ?: '-' }}</td>
                                    <td class="px-3 py-4 align-top">@if ($supplierQuote->is_selected)<span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">{{ __('Recommended') }}</span>@else<span class="text-slate-400">{{ __('No') }}</span>@endif</td>
                                    <td class="px-3 py-4 align-top">
                                        <div class="flex flex-col gap-2">
                                            <form method="POST" action="{{ route('bpp.supplier-quotes.select', [$bpp, $supplierQuote]) }}">@csrf @method('PUT')<button type="submit" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">{{ __('Select Supplier') }}</button></form>
                                            <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50" x-on:click="editQuoteModalOpen = true">{{ __('Edit') }}</button>
                                            <form method="POST" action="{{ route('bpp.supplier-quotes.destroy', [$bpp, $supplierQuote]) }}">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center rounded-md border border-rose-200 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-rose-700 transition hover:bg-rose-50">{{ __('Delete') }}</button></form>
                                        </div>
                                        <template x-if="editQuoteModalOpen">
                                            <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
                                                <div class="absolute inset-0 bg-slate-900/40" x-on:click="editQuoteModalOpen = false"></div>
                                                <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <div>
                                                            <p class="text-sm text-slate-500">{{ __('Edit Supplier Entry') }}</p>
                                                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ __('C1 - Kajian Pasaran') }}</h3>
                                                        </div>
                                                        <button type="button" class="project-modal-close" x-on:click="editQuoteModalOpen = false">{{ __('Close') }}</button>
                                                    </div>
                                                    <form method="POST" action="{{ route('bpp.supplier-quotes.update', [$bpp, $supplierQuote]) }}" class="mt-6 grid gap-4 md:grid-cols-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <div><x-input-label :for="'quote-supplier-'.$supplierQuote->id" :value="__('Supplier Name')" /><x-text-input :id="'quote-supplier-'.$supplierQuote->id" name="supplier_name" type="text" class="mt-1 block w-full" :value="$supplierQuote->supplier_name" /></div>
                                                        <div><x-input-label :for="'quote-total-'.$supplierQuote->id" :value="__('Total Price')" /><x-text-input :id="'quote-total-'.$supplierQuote->id" name="total_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$supplierQuote->total_price" /></div>
                                                        <div><x-input-label :for="'quote-delivery-'.$supplierQuote->id" :value="__('Delivery Period')" /><x-text-input :id="'quote-delivery-'.$supplierQuote->id" name="delivery_period" type="text" class="mt-1 block w-full" :value="$supplierQuote->delivery_period" /></div>
                                                        <div><x-input-label :for="'quote-validity-'.$supplierQuote->id" :value="__('Validity Period')" /><x-text-input :id="'quote-validity-'.$supplierQuote->id" name="validity_period" type="text" class="mt-1 block w-full" :value="$supplierQuote->validity_period" /></div>
                                                        <div class="md:col-span-2"><x-input-label :for="'quote-ref-'.$supplierQuote->id" :value="__('Quotation Reference')" /><x-text-input :id="'quote-ref-'.$supplierQuote->id" name="quotation_reference" type="text" class="mt-1 block w-full" :value="$supplierQuote->quotation_reference" /></div>
                                                        <div class="md:col-span-2 flex items-center gap-3 pt-2">
                                                            <button type="button" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50" x-on:click="editQuoteModalOpen = false">{{ __('Cancel') }}</button>
                                                            <x-primary-button>{{ __('Save Supplier') }}</x-primary-button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">{{ __('No supplier comparison entries added yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <p class="project-tree-label">{{ __('Add Supplier Entry') }}</p>
                    <form method="POST" action="{{ route('bpp.supplier-quotes.store', $bpp) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div><x-input-label for="supplier_name" :value="__('Supplier Name')" /><x-text-input id="supplier_name" name="supplier_name" type="text" class="mt-1 block w-full" :value="old('supplier_name')" /></div>
                        <div><x-input-label for="total_price" :value="__('Total Price')" /><x-text-input id="total_price" name="total_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('total_price')" /></div>
                        <div><x-input-label for="delivery_period" :value="__('Delivery Period')" /><x-text-input id="delivery_period" name="delivery_period" type="text" class="mt-1 block w-full" :value="old('delivery_period')" /></div>
                        <div><x-input-label for="validity_period" :value="__('Validity Period')" /><x-text-input id="validity_period" name="validity_period" type="text" class="mt-1 block w-full" :value="old('validity_period')" /></div>
                        <div class="md:col-span-2"><x-input-label for="quotation_reference" :value="__('Quotation Reference')" /><x-text-input id="quotation_reference" name="quotation_reference" type="text" class="mt-1 block w-full" :value="old('quotation_reference')" /></div>
                        <div class="md:col-span-2">
                            <x-input-error class="mt-2" :messages="$errors->get('supplier_name')" />
                            <x-input-error class="mt-2" :messages="$errors->get('total_price')" />
                            <x-input-error class="mt-2" :messages="$errors->get('delivery_period')" />
                            <x-input-error class="mt-2" :messages="$errors->get('validity_period')" />
                            <x-input-error class="mt-2" :messages="$errors->get('quotation_reference')" />
                        </div>
                        <div class="md:col-span-2"><button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">{{ __('Add Supplier') }}</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
