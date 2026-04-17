<section class="print-page">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('Senarai Semak') }}</h2>
            <p class="print-form-subtitle">{{ __('Borang Permohonan Perolehan') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('No. Semakan') }}</span><strong>{{ $bpp->no_semakan ?: '01' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Tarikh Kuat Kuasa') }}</span><strong>{{ $bpp->tarikh_kuat_kuasa ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ $bpp->muka_surat ?: '1 / 1' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <div class="print-grid-two">
            <div class="print-kv"><span>{{ __('Tajuk Draft') }}</span><strong>{{ $bpp->title }}</strong></div>
            <div class="print-kv"><span>{{ __('Status') }}</span><strong>{{ strtoupper($bpp->status) }}</strong></div>
            <div class="print-kv"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
            <div class="print-kv"><span>{{ __('Tajuk Perolehan') }}</span><strong>{{ $bpp->b1_tajuk_perolehan ?: '-' }}</strong></div>
        </div>
    </div>

    <table class="print-table mt-6">
        <thead>
            <tr>
                <th style="width: 3.75rem;">{{ __('Bil.') }}</th>
                <th>{{ __('Perkara') }}</th>
                <th style="width: 6rem;">{{ __('Semakan') }}</th>
                <th style="width: 17rem;">{{ __('Catatan') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($checklistItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td>
                        <span class="print-checkbox">{{ $item['checked'] ? '/' : '' }}</span>
                    </td>
                    <td>{{ $item['note'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6 print-note-box">
        <p class="print-section-label">{{ __('Status Readiness Semasa') }}</p>
        <p class="mt-2 text-sm font-semibold text-slate-900">{{ __($validationResult['state']['label']) }}</p>
        <p class="mt-1 text-sm text-slate-600">{{ __($validationResult['state']['message']) }}</p>
    </div>
</section>
