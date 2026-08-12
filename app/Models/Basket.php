<?php

namespace App\Models;

use Database\Factories\BasketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $description
 * @property string|null $image
 * @property int $price
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'type', 'description', 'image', 'price', 'is_active', 'sort_order'])]
class Basket extends Model
{
    /** @use HasFactory<BasketFactory> */
    use HasFactory;

    public const TYPE_WELLNESS = 'wellness';

    public const TYPE_SABJIFY = 'sabjify';

    public const TYPES = [
        self::TYPE_WELLNESS => 'Wellness Basket',
        self::TYPE_SABJIFY => 'Sabjify Basket',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Product, $this, BasketProduct, 'pivot'>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'basket_product')
            ->using(BasketProduct::class)
            ->withPivot('unit', 'price')
            ->withTimestamps();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return filter_var($this->image, FILTER_VALIDATE_URL) !== false
            ? $this->image
            : Storage::url($this->image);
    }

    public function displayImageUrl(): string
    {
        return $this->imageUrl() ?? config('mart.placeholder_image');
    }

    public function imageFit(): string
    {
        return str_ends_with(strtolower((string) $this->image), '.png') ? 'object-contain' : 'object-cover';
    }

    /**
     * @param  Builder<Basket>  $query
     * @return Builder<Basket>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Basket>  $query
     * @return Builder<Basket>
     */
    public function scopeWellness(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_WELLNESS);
    }

    /**
     * @param  Builder<Basket>  $query
     * @return Builder<Basket>
     */
    public function scopeSabjify(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SABJIFY);
    }
}
