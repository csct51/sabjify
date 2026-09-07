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
 * @property int|null $mrp
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'type', 'description', 'image', 'price', 'mrp', 'is_active', 'sort_order'])]
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

    public function discountPercent(): int
    {
        if (! $this->mrp || $this->mrp <= $this->price) {
            return 0;
        }

        return (int) round((($this->mrp - $this->price) / $this->mrp) * 100);
    }

    /**
     * @return BelongsToMany<Product, $this, BasketProduct, 'pivot'>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'basket_product')
            ->using(BasketProduct::class)
            ->withPivot('product_unit_id', 'unit', 'price')
            ->withTimestamps();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Base units of a constituent line per basket. Custom numeric qtys
     * composed as "{number} {purchaseUnit}" resolve exactly; dropdown lines
     * convert via the linked unit; legacy free text falls back to factorFor().
     */
    public static function baseShareFor(Product $product, ?int $unitId, ?string $unitName): float
    {
        $purchaseUnit = $product->purchaseUnit();
        $name = is_string($unitName) ? trim($unitName) : '';

        if ($name !== '' && preg_match('/^(\d+(?:\.\d+)?)\s+'.preg_quote($purchaseUnit, '/').'$/i', $name, $matches)) {
            return round((float) $matches[1] * (Unit::factorFor($purchaseUnit) ?? 1.0), 3);
        }

        if ($unitId) {
            $unit = $product->relationLoaded('units')
                ? $product->units->firstWhere('id', $unitId)
                : ProductUnit::where('product_id', $product->id)->find($unitId);

            if ($unit) {
                return round(Unit::toBaseQty($unit->unit, 1), 3);
            }
        }

        if (is_string($unitName) && trim($unitName) !== '') {
            return round(Unit::toBaseQty(trim($unitName), 1), 3);
        }

        return 0.0;
    }

    /**
     * Base units consumed per basket: [product_id => baseQty].
     *
     * @return array<int, float>
     */
    public function constituentShares(): array
    {
        $shares = [];

        foreach ($this->products()->with('units')->get() as $product) {
            $share = self::baseShareFor($product, $product->pivot->product_unit_id, $product->pivot->unit);

            if ($share > 0) {
                $shares[$product->id] = $share;
            }
        }

        return $shares;
    }

    /**
     * Whole baskets sellable from current constituent stocks.
     */
    public function basketsSellable(): int
    {
        $min = null;

        foreach ($this->products()->with('units')->get() as $product) {
            $share = self::baseShareFor($product, $product->pivot->product_unit_id, $product->pivot->unit);

            if ($share <= 0) {
                continue;
            }

            $packs = (int) floor((float) $product->current_stock / $share);
            $min = $min === null ? $packs : min($min, $packs);
        }

        return max(0, $min ?? 0);
    }

    /**
     * Effective constituent units all toggled on (unlinked lines default on).
     */
    public function constituentsInStock(): bool
    {
        foreach ($this->products()->with('units')->get() as $product) {
            $unitId = $product->pivot->product_unit_id;

            if ($unitId) {
                $unit = $product->units->firstWhere('id', $unitId);

                if ($unit && ! $unit->in_stock) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Full sale gate: basket active, constituents toggled on, stock covers.
     */
    public function canSell(int $baskets = 1): bool
    {
        return $this->is_active && $this->constituentsInStock() && $this->basketsSellable() >= $baskets;
    }

    /**
     * @return BelongsToMany<Recipe, $this>
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'basket_recipe')->withTimestamps();
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
