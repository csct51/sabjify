<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $product_id
 * @property int|null $product_unit_id
 * @property int|null $recipe_id
 * @property int|null $basket_id
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProductUnit|null $productUnit
 */
#[Fillable(['user_id', 'product_id', 'product_unit_id', 'recipe_id', 'basket_id', 'quantity'])]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductUnit, $this>
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    /**
     * @return BelongsTo<Recipe, $this>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * @return BelongsTo<Basket, $this>
     */
    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function name(): string
    {
        if ($this->basket) {
            return $this->basket->name;
        }

        return $this->product?->name ?? 'Removed item';
    }

    public function isOrphaned(): bool
    {
        return ! $this->basket && ! $this->product;
    }

    public function unitName(): ?string
    {
        return $this->productUnit?->unit ?? ($this->product ? $this->product->unit : null);
    }

    public function unitPrice(): int
    {
        if ($this->basket) {
            return $this->basket->price;
        }

        return $this->productUnit?->price ?? ($this->product?->price ?? 0);
    }

    public function total(): int
    {
        return $this->unitPrice() * $this->quantity;
    }
}
