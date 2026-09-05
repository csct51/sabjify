<?php

namespace App\Models;

use Database\Factories\WastageItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastageItem extends Model
{
    /** @use HasFactory<WastageItemFactory> */
    use HasFactory;

    protected $fillable = [
        'wastage_id',
        'product_id',
        'unit',
        'qty',
        'base_qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'base_qty' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<Wastage, $this>
     */
    public function wastage(): BelongsTo
    {
        return $this->belongsTo(Wastage::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
