<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBppRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'b3_nilai_tawaran_perolehan' => $this->normalizeCurrency($this->input('b3_nilai_tawaran_perolehan')),
            'b4_harga_indikatif' => $this->normalizeCurrency($this->input('b4_harga_indikatif')),
            'b5_peruntukan_diluluskan' => $this->normalizeCurrency($this->input('b5_peruntukan_diluluskan')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $procurementMethodKeys = implode(',', array_keys((new \App\Models\Bpp)->procurementMethodOptions()));

        return [
            'title' => ['required', 'string', 'max:255'],
            'no_rujukan_perolehan' => ['nullable', 'string', 'max:255'],
            'tajuk_dokumen' => ['nullable', 'string', 'max:255'],
            'ruj_dokumen' => ['nullable', 'string', 'max:255'],
            'no_semakan' => ['nullable', 'string', 'max:255'],
            'tarikh_kuat_kuasa' => ['nullable', 'string', 'max:255'],
            'muka_surat' => ['nullable', 'string', 'max:255'],
            'a1_nama_pemohon' => ['nullable', 'string', 'max:255'],
            'a2_jawatan_gred' => ['nullable', 'string', 'max:255'],
            'a3_jabatan_institusi' => ['nullable', 'string', 'max:255'],
            'a4_no_tel_email' => ['nullable', 'string', 'max:255'],
            'kaedah_perolehan' => ['nullable', 'in:'.$procurementMethodKeys],
            'b1_tajuk_perolehan' => ['nullable', 'string', 'max:255'],
            'b2_kategori_perolehan' => ['nullable', 'string', 'max:255'],
            'b3_nilai_tawaran_perolehan' => ['nullable', 'numeric', 'min:0'],
            'b4_harga_indikatif' => ['nullable', 'numeric', 'min:0'],
            'b5_peruntukan_diluluskan' => ['nullable', 'numeric', 'min:0'],
            'b6_justifikasi_keperluan' => ['nullable', 'string'],
            'b7_tajuk_asal_perolehan' => ['nullable', 'string', 'max:255'],
            'b8_tarikh_diperlukan' => ['nullable', 'date_format:Y-m'],
            'b9_lokasi_diperlukan' => ['nullable', 'string', 'max:255'],
            'd_nama_pembekal' => ['nullable', 'string', 'max:255'],
            'd_alamat_pembekal' => ['nullable', 'string', 'max:255'],
            'd_no_pendaftaran_syarikat' => ['nullable', 'string', 'max:255'],
            'd_kriteria_pemilihan' => ['nullable', 'string'],
            'd_lain_lain_kriteria' => ['nullable', 'string'],
            'c1_selection_reason' => ['nullable', 'string', 'max:255'],
            'c1_selection_reason_lain_lain' => ['nullable', 'string'],
        ];
    }

    private function normalizeCurrency(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
