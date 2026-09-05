<?php

namespace App\Models;

use App\Casts\CommaSeparatedArray;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $unit
 * @property int $price
 * @property int|null $mrp
 * @property string|null $image
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property numeric $current_stock
 * @property string|null $base_unit
 * @property numeric|null $low_stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ProductUnit> $units
 * @property-read BasketProduct $pivot
 */
#[Fillable(['category_id', 'name', 'slug', 'description', 'alternate_names', 'unit', 'price', 'mrp', 'image', 'is_active', 'is_featured', 'sort_order', 'current_stock', 'base_unit', 'low_stock'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'alternate_names' => CommaSeparatedArray::class,
            'current_stock' => 'decimal:3',
            'low_stock' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * @return HasMany<ProductUnit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class)->orderBy('sort_order')->orderBy('price');
    }

    public function defaultUnit(): ?ProductUnit
    {
        return $this->units()->first();
    }

    public function minPrice(): int
    {
        return $this->units()->min('price') ?? $this->price;
    }

    public function hasMultipleUnits(): bool
    {
        return $this->units()->count() > 1;
    }

    public function baseUnit(): ?string
    {
        if (is_string($this->base_unit) && $this->base_unit !== '') {
            return $this->base_unit;
        }

        // Legacy fallback for rows predating the base_unit column.
        $unit = $this->relationLoaded('units') ? $this->units->first() : $this->units()->first();
        $name = $unit?->unit ?? $this->unit;

        $unitModel = Unit::where('name', $name)->first();

        if ($unitModel && is_string($unitModel->base_unit) && $unitModel->base_unit !== '') {
            return $unitModel->base_unit;
        }

        return null;
    }

    public function baseRow(): ?Unit
    {
        $base = $this->baseUnit();

        return $base ? Unit::baseRow($base) : null;
    }

    /**
     * Stock expressed in a selling unit (e.g. 2500 g as 5 x 500 g).
     */
    public function stockInUnit(string $unitName): float
    {
        $factor = Unit::factorFor($unitName);

        if (! $factor || $factor <= 0) {
            return 0.0;
        }

        return round((float) $this->current_stock / $factor, 3);
    }

    public function purchaseUnit(): string
    {
        $purchase = $this->baseRow()?->purchase_unit;

        if (is_string($purchase) && $purchase !== '') {
            return $purchase;
        }

        $base = $this->baseUnit();

        if ($base) {
            return $base;
        }

        $unit = $this->relationLoaded('units') ? $this->units->first() : $this->units()->first();
        $name = $unit?->unit ?? $this->unit;

        // Fallback for legacy unit strings
        $lower = strtolower($name);

        if (str_contains($lower, 'kg') || str_contains($lower, 'g')) {
            return 'kg';
        }

        return 'piece';
    }

    public function displayStock(): string
    {
        $stock = (float) $this->current_stock;
        $display = rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.');

        if ($display === '') {
            $display = '0';
        }

        $base = $this->baseUnit() ?? '';
        $purchase = $this->purchaseUnit();

        if ($purchase !== $base && $base !== '') {
            $factor = Unit::factorFor($purchase) ?? 1.0;
            $converted = rtrim(rtrim(number_format($stock / $factor, 3, '.', ''), '0'), '.');

            return ($converted === '' ? '0' : $converted).' '.$purchase.' ('.$display.' '.$base.')';
        }

        return trim($display.' '.$purchase);
    }

    public function isLowStock(): bool
    {
        if ($this->low_stock === null) {
            return false;
        }

        $stock = (float) $this->current_stock;

        return $stock > 0 && $stock <= (float) $this->low_stock + 1e-9;
    }

    /**
     * Whole packs sellable in the given selling unit from current base stock.
     * Toggle is checked separately by callers (unchanged semantics).
     */
    public function sellablePacksFor(?ProductUnit $unit): int
    {
        $name = $unit?->unit ?? $this->unit;
        $factor = Unit::factorFor($name) ?? 1.0;

        if ($factor <= 0) {
            return 0;
        }

        return max(0, (int) floor((float) $this->current_stock / $factor));
    }

    /**
     * Base units needed for the given packs of a selling unit.
     */
    public function baseNeededFor(?ProductUnit $unit, int $packs): float
    {
        $name = $unit?->unit ?? $this->unit;
        $factor = Unit::factorFor($name) ?? 1.0;

        return round($packs * $factor, 3);
    }

    /**
     * Pack count at/below which the "Only X left" nudge shows. Uses the
     * admin low-stock threshold converted to packs when set, else 5.
     */
    public function lowPacksThreshold(?ProductUnit $unit = null): int
    {
        if ($this->low_stock !== null) {
            $name = $unit?->unit ?? $this->unit;
            $factor = Unit::factorFor($name) ?? 1.0;

            if ($factor > 0) {
                return max(1, (int) ceil((float) $this->low_stock / $factor));
            }
        }

        return 5;
    }

    public function inStock(): bool
    {
        return $this->relationLoaded('units')
            ? $this->units->contains(fn (ProductUnit $unit) => $unit->in_stock)
            : $this->units()->where('in_stock', true)->exists();
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

    public function placeholderImageUrl(): string
    {
        $text = urlencode(Str::limit($this->name, 24, ''));

        return config('mart.placeholder_image').'?text='.$text;
    }

    public function displayImageUrl(): string
    {
        return $this->imageUrl() ?? $this->placeholderImageUrl();
    }

    public function imageFit(): string
    {
        return str_ends_with(strtolower((string) $this->image), '.png') ? 'object-contain' : 'object-cover';
    }

    public function emoji(): string
    {
        return match (strtolower($this->category->name ?? '')) {
            'fruits' => '🍎',
            'vegetables' => '🥦',
            'leafy greens' => '🥬',
            'exotic fruits' => '🥝',
            'herbs' => '🌿',
            'root vegetables' => '🥕',
            default => '🥗',
        };
    }

    public function discountPercent(): int
    {
        if (! $this->mrp || $this->mrp <= $this->price) {
            return 0;
        }

        return (int) round((($this->mrp - $this->price) / $this->mrp) * 100);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereHas('units', fn ($q) => $q->where('in_stock', true));
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereNotNull('low_stock')
            ->where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'low_stock');
    }
}
