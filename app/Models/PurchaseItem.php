<?php

namespace App\Models;

use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit',
        'rate',
        'qty',
        'line_total',
        'base_qty',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'integer',
            'qty' => 'decimal:3',
            'line_total' => 'integer',
            'base_qty' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<Purchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
