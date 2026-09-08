<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property int|null $basket_id
 * @property string $product_name
 * @property string|null $unit
 * @property int $price
 * @property int $quantity
 * @property int $total
 * @property numeric|null $base_qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['order_id', 'product_id', 'basket_id', 'product_name', 'unit', 'price', 'quantity', 'total', 'base_qty'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_qty' => 'decimal:3',
        ];
    }

    /**
     * Base-unit quantity sold (snapshot; falls back to live conversion for
     * rows predating the base_qty snapshot).
     */
    public function soldBaseQty(): float
    {
        if ($this->base_qty !== null) {
            return (float) $this->base_qty;
        }

        $factor = $this->unit ? Unit::factorFor($this->unit) : null;

        return round((float) $this->quantity * ($factor ?? 1.0), 3);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Basket, $this>
     */
    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    /**
     * @return BelongsTo<ProductUnit, $this>
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
