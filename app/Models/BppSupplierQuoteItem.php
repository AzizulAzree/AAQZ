<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BppSupplierQuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bpp_id',
        'bpp_supplier_quote_id',
        'line_number',
        'item_spesifikasi',
        'kuantiti',
        'unit_ukuran',
        'harga_tawaran',
        'jumlah_harga',
    ];

    protected function casts(): array
    {
        return [
            'bpp_id' => 'integer',
            'bpp_supplier_quote_id' => 'integer',
            'line_number' => 'integer',
            'kuantiti' => 'decimal:2',
            'harga_tawaran' => 'decimal:2',
            'jumlah_harga' => 'decimal:2',
        ];
    }

    public function bpp(): BelongsTo
    {
        return $this->belongsTo(Bpp::class);
    }

    public function supplierQuote(): BelongsTo
    {
        return $this->belongsTo(BppSupplierQuote::class, 'bpp_supplier_quote_id');
    }
}
