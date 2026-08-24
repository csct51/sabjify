<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $basket_id
 * @property int $product_id
 * @property int|null $product_unit_id
 * @property string|null $unit
 * @property int|null $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BasketProduct extends Pivot
{
    protected $fillable = [
        'basket_id',
        'product_id',
        'product_unit_id',
        'unit',
        'price',
    ];
}
