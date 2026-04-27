<section class="{{ !empty($packagePdfMode) ? 'c1-page pdf-package-page' : 'print-page c1-page' }}">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('C1 - Kajian Pasaran') }}</h2>
            <p class="print-form-subtitle">{{ __('Perbandingan Sebut Harga Pembekal') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Kategori Perolehan') }}</span><strong>{{ $bpp->b2_kategori_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ 'C1 / 1' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <div class="print-grid-two">
            <div class="print-kv"><span>{{ __('Tajuk Perolehan') }}</span><strong>{{ $bpp->b1_tajuk_perolehan ?: '-' }}</strong></div>
            <div class="print-kv"><span>{{ __('Pembekal Disyorkan') }}</span><strong>{{ $selectedSupplier?->supplier_name ?: '-' }}</strong></div>
        </div>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('Perbandingan Pembekal') }}</p>
        <div class="print-table-wrap">
            <table class="print-table print-table-dense">
                <thead>
                    <tr>
                        <th style="width: 3.5rem;">{{ __('Bil.') }}</th>
                        <th>{{ __('Nama Pembekal') }}</th>
                        <th style="width: 6.5rem;">{{ __('Jumlah Harga (RM)') }}</th>
                        <th style="width: 7rem;">{{ __('Tempoh Penyerahan') }}</th>
                        <th style="width: 7rem;">{{ __('Tempoh Sah Laku') }}</th>
                        <th style="width: 7rem;">{{ __('Rujukan Sebut Harga') }}</th>
                        <th style="width: 5.5rem;">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplierQuotes as $index => $quote)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="print-cell-wrap">{{ $quote->supplier_name }}</td>
                            <td>{{ number_format((float) $quote->total_price, 2, '.', '') }}</td>
                            <td class="print-cell-wrap">{{ $quote->delivery_period }}</td>
                            <td class="print-cell-wrap">{{ $quote->validity_period }}</td>
                            <td class="print-cell-wrap">{{ $quote->quotation_reference ?: '-' }}</td>
                            <td>
                                @if ($quote->is_selected)
                                    <span class="print-selected-pill">{{ __('Dipilih') }}</span>
                                @else
                                    {{ __('Dinilai') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="print-cell-wrap">{{ __('Tiada pembekal direkodkan untuk C1 pada masa ini.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('Sebab Pemilihan Pembekal Disyorkan') }}</p>
        <table class="print-table">
            <tbody>
                <tr>
                    <th style="width: 16rem;">{{ __('Kriteria Pemilihan') }}</th>
                    <td class="print-cell-wrap">{{ $bpp->d_kriteria_pemilihan ?: ($bpp->selectionReasonLabel() ?: '-') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Lain-lain Kriteria') }}</th>
                    <td class="print-cell-wrap">{{ $bpp->d_lain_lain_kriteria ?: ($bpp->c1_selection_reason_lain_lain ?: '-') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 print-totals-grid">
        <div class="print-total-box">
            <span>{{ __('Jumlah Pembekal Direkodkan') }}</span>
            <strong>{{ $supplierQuotes->count() }}</strong>
        </div>
        <div class="print-total-box">
            <span>{{ __('Jumlah Harga Pembekal Dipilih') }}</span>
            <strong>{{ $selectedSupplier ? number_format((float) $selectedSupplier->total_price, 2, '.', '') : '-' }}</strong>
        </div>
    </div>
</section>
