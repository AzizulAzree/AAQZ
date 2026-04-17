<div class="print-table-wrap">
    <table class="print-table print-table-dense">
        <thead>
            <tr>
                <th style="width: 3.5rem;">{{ __('Bil.') }}</th>
                <th>{{ __('Item / Spesifikasi') }}</th>
                <th style="width: 5.5rem;">{{ __('Kuantiti') }}</th>
                <th style="width: 5.5rem;">{{ __('Unit') }}</th>
                <th style="width: 6.5rem;">{{ __('Harga Seunit (RM)') }}</th>
                <th style="width: 6.5rem;">{{ __('Jumlah Harga (RM)') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appendixRows as $row)
                <tr>
                    <td>{{ $row->line_number }}</td>
                    <td class="print-cell-wrap">{{ $row->item_spesifikasi }}</td>
                    <td>{{ number_format((float) $row->kuantiti, 2, '.', '') }}</td>
                    <td class="print-cell-wrap">{{ $row->unit_ukuran }}</td>
                    <td>{{ number_format((float) $row->harga_seunit, 2, '.', '') }}</td>
                    <td>{{ number_format((float) $row->jumlah_harga, 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="print-cell-wrap">{{ __('Tiada baris item direkodkan untuk lampiran ini.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
