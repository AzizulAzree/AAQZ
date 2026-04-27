<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BppSupplierQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'bpp_id',
        'supplier_name',
        'registration_number',
        'supplier_address',
        'total_price',
        'delivery_period',
        'validity_period',
        'quotation_reference',
        'is_selected',
    ];

    protected function casts(): array
    {
        return [
            'bpp_id' => 'integer',
            'total_price' => 'decimal:2',
            'is_selected' => 'boolean',
        ];
    }

    public function bpp(): BelongsTo
    {
        return $this->belongsTo(Bpp::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BppSupplierQuoteItem::class, 'bpp_supplier_quote_id')->orderBy('line_number');
    }
}
