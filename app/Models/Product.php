<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
 * @property int $stock
 * @property string|null $image
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category_id', 'name', 'slug', 'description', 'unit', 'price', 'mrp', 'stock', 'image', 'is_active', 'is_featured', 'sort_order'])]
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

    public function inStock(): bool
    {
        return $this->stock > 0;
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
        return $query->where('is_active', true)->where('stock', '>', 0);
    }
}
