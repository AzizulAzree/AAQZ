<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bpp extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'no_rujukan_perolehan',
        'tajuk_dokumen',
        'ruj_dokumen',
        'no_semakan',
        'tarikh_kuat_kuasa',
        'muka_surat',
        'a1_nama_pemohon',
        'a2_jawatan_gred',
        'a3_jabatan_institusi',
        'a4_no_tel_email',
        'kaedah_perolehan',
        'b1_tajuk_perolehan',
        'b2_kategori_perolehan',
        'b3_nilai_tawaran_perolehan',
        'b4_harga_indikatif',
        'b5_peruntukan_diluluskan',
        'b6_justifikasi_keperluan',
        'b7_tajuk_asal_perolehan',
        'b8_tarikh_diperlukan',
        'b9_lokasi_diperlukan',
        'd_nama_pembekal',
        'd_alamat_pembekal',
        'd_no_pendaftaran_syarikat',
        'd_kriteria_pemilihan',
        'd_lain_lain_kriteria',
        'c1_selection_reason',
        'c1_selection_reason_lain_lain',
        'quotation_extraction_format_version',
        'quotation_extraction_raw_text',
        'quotation_extraction_review',
    ];

    protected function casts(): array
    {
        return [
            'b3_nilai_tawaran_perolehan' => 'decimal:2',
            'b4_harga_indikatif' => 'decimal:2',
            'b5_peruntukan_diluluskan' => 'decimal:2',
            'b8_tarikh_diperlukan' => 'date',
            'quotation_extraction_review' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $bpp): void {
            $bpp->tajuk_dokumen ??= 'Borang Permohonan Perolehan';
            $bpp->ruj_dokumen ??= 'NIBM/F/PRC/02/01';
            $bpp->no_semakan ??= '01';
        });
    }

    public function appendixRows(): HasMany
    {
        return $this->hasMany(BppAppendixRow::class)->orderBy('line_number');
    }

    public function supplierQuotes(): HasMany
    {
        return $this->hasMany(BppSupplierQuote::class)->latest();
    }

    public static function appendixTypeForCategory(?string $category): ?string
    {
        return match (mb_strtolower(trim((string) $category))) {
            'bekalan' => 'c2',
            'perkhidmatan' => 'c3',
            'kerja' => 'c4',
            default => null,
        };
    }

    public static function appendixLabelForType(?string $appendixType): ?string
    {
        return match ($appendixType) {
            'c2' => 'C2 - Perbekalan',
            'c3' => 'C3 - Perkhidmatan',
            'c4' => 'C4 - Kerja',
            default => null,
        };
    }

    public function activeAppendixType(): ?string
    {
        return self::appendixTypeForCategory($this->b2_kategori_perolehan);
    }

    public function activeAppendixLabel(): ?string
    {
        return self::appendixLabelForType($this->activeAppendixType());
    }

    public function nextAppendixLineNumber(string $appendixType): int
    {
        return (int) $this->appendixRows()
            ->where('appendix_type', $appendixType)
            ->max('line_number') + 1;
    }

    public function resequenceAppendixRows(string $appendixType): void
    {
        $this->appendixRows()
            ->where('appendix_type', $appendixType)
            ->get()
            ->values()
            ->each(function (BppAppendixRow $row, int $index): void {
                $row->updateQuietly([
                    'line_number' => $index + 1,
                ]);
            });
    }

    public function syncAppendixGrandTotal(?string $appendixType = null): void
    {
        $appendixType ??= $this->activeAppendixType();

        if ($appendixType === null) {
            return;
        }

        $grandTotal = (float) $this->appendixRows()
            ->where('appendix_type', $appendixType)
            ->sum('jumlah_harga');

        $this->updateQuietly([
            'b3_nilai_tawaran_perolehan' => round($grandTotal, 2),
        ]);
    }

    public function selectedSupplierQuote(): ?BppSupplierQuote
    {
        return $this->supplierQuotes()->where('is_selected', true)->first();
    }

    public function syncSelectedSupplierQuote(): void
    {
        $selectedQuote = $this->selectedSupplierQuote();

        if ($selectedQuote === null) {
            $this->clearSelectedSupplierSync();

            return;
        }

        $this->updateQuietly([
            'd_nama_pembekal' => $selectedQuote->supplier_name,
            'd_kriteria_pemilihan' => $this->selectionReasonLabel(),
            'd_lain_lain_kriteria' => $this->c1_selection_reason === 'Lain-lain'
                ? $this->c1_selection_reason_lain_lain
                : null,
        ]);
    }

    public function clearSelectedSupplierSync(): void
    {
        $this->updateQuietly([
            'd_nama_pembekal' => null,
            'd_kriteria_pemilihan' => null,
            'd_lain_lain_kriteria' => null,
        ]);
    }

    public function selectionReasonOptions(): array
    {
        return [
            'Tawaran harga terbaik',
            'Keupayaan teknikal dan kewangan',
            'Pengalaman dan rekod prestasi',
            'Keupayaan operasi dan sumber',
            'Tempoh pembekalan/perlaksanaan yang munasabah',
            'Pembekal Tunggal',
            'Lain-lain',
        ];
    }

    public function selectionReasonLabel(): ?string
    {
        if (! in_array($this->c1_selection_reason, $this->selectionReasonOptions(), true)) {
            return null;
        }

        return $this->c1_selection_reason;
    }

    public function procurementMethodOptions(): array
    {
        return [
            'pembelian_terus' => 'Pembelian Terus (sehingga tidak melebihi RM50,000.00)',
            'pembekal_tunggal_bawah_50k' => 'Pembekal Tunggal (sehingga tidak melebihi RM50,000.00)',
            'sebut_harga' => 'Sebut Harga (RM50,000.00 sehingga tidak melebihi RM500,000.00)',
            'tender' => 'Tender (melebihi RM500,000.00)',
            'pembekal_tunggal_rundingan_terus' => 'Pembekal Tunggal / Rundingan Terus (melebihi RM50,000.00)',
        ];
    }

    public function procurementMethodLabel(): ?string
    {
        $options = $this->procurementMethodOptions();

        return $options[$this->kaedah_perolehan] ?? null;
    }

    public function displayCurrency(float|string|null $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return 'RM '.number_format((float) $amount, 2, '.', ',');
    }

    public function procurementRequiredMonthValue(): ?string
    {
        return $this->b8_tarikh_diperlukan?->format('Y-m');
    }

    public function procurementRequiredMonthLabel(): ?string
    {
        return $this->b8_tarikh_diperlukan?->format('M-y');
    }
}
