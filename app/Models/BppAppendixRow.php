<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BppAppendixRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'bpp_id',
        'appendix_type',
        'line_number',
        'item_spesifikasi',
        'kuantiti',
        'unit_ukuran',
        'harga_seunit',
        'jumlah_harga',
    ];

    protected function casts(): array
    {
        return [
            'bpp_id' => 'integer',
            'line_number' => 'integer',
            'kuantiti' => 'decimal:2',
            'harga_seunit' => 'decimal:2',
            'jumlah_harga' => 'decimal:2',
        ];
    }

    public function bpp(): BelongsTo
    {
        return $this->belongsTo(Bpp::class);
    }
}
