<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property string|null $base_unit
 * @property float $to_base_factor
 * @property bool $is_base
 * @property string|null $purchase_unit
 * @property bool $integer_only
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'sort_order', 'base_unit', 'to_base_factor', 'is_base', 'purchase_unit', 'integer_only'])]

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit', 'name');
    }

    /**
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    protected function casts(): array
    {
        return [
            'to_base_factor' => 'decimal:4',
            'is_base' => 'boolean',
            'integer_only' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeIsBase(Builder $query): Builder
    {
        return $query->where('is_base', true);
    }

    /**
     * Canonical base row for a base name (e.g. 'g').
     */
    public static function baseRow(string $base): ?self
    {
        return static::where('name', $base)->where('is_base', true)->first();
    }

    /**
     * Entry-unit names admins type quantities in (one per base row).
     *
     * @return array<int, string>
     */
    public static function purchaseUnitOptions(): array
    {
        return static::isBase()->whereNotNull('purchase_unit')->orderBy('sort_order')->pluck('purchase_unit')->all();
    }

    /**
     * Whether quantities in the given entry unit must be whole numbers.
     */
    public static function integerOnlyFor(string $purchaseUnit): bool
    {
        $base = static::isBase()->where('purchase_unit', $purchaseUnit)->first();

        if ($base) {
            return (bool) $base->integer_only;
        }

        return strtolower($purchaseUnit) === 'piece';
    }

    /**
     * Placeholder example for quantity inputs in the given entry unit.
     */
    public static function qtyPlaceholderFor(string $purchaseUnit): string
    {
        return static::integerOnlyFor($purchaseUnit) ? 'e.g. 2' : 'e.g. 0.5';
    }

    /**
     * Helper text for quantity inputs in the given entry unit.
     */
    public static function qtyHintFor(string $purchaseUnit): string
    {
        if (static::integerOnlyFor($purchaseUnit)) {
            return 'Whole '.Str::plural($purchaseUnit).' only.';
        }

        $base = static::isBase()->where('purchase_unit', $purchaseUnit)->value('name');

        if (is_string($base) && $base !== '' && strcasecmp($base, $purchaseUnit) !== 0) {
            return "Enter {$purchaseUnit} — e.g. 0.5 = 500 {$base}.";
        }

        return "Enter {$purchaseUnit} — decimals allowed.";
    }

    /**
     * Conversion factor for a unit name, with code fallback so a missing
     * units row can never silently corrupt stock math again.
     */
    public static function factorFor(string $name): ?float
    {
        $unit = static::where('name', $name)->first();

        if ($unit && (float) $unit->to_base_factor > 0) {
            return (float) $unit->to_base_factor;
        }

        return match (strtolower($name)) {
            'kg' => 1000.0,
            'g' => 1.0,
            'litre', 'l' => 1000.0,
            'ml' => 1.0,
            'piece', 'pc', '1 pc' => 1.0,
            default => null,
        };
    }

    /**
     * Shopper-facing unit text: sub-1 kg/litre amounts render in the base
     * sub-unit ("0.75 kg" → "750 g"); everything else returned untouched.
     */
    public static function displayUnitFor(string $unitName): string
    {
        $name = trim($unitName);

        if (! preg_match('/^(\d+(?:\.\d+)?)\s+(.+)$/', $name, $matches)) {
            return $unitName;
        }

        $qty = (float) $matches[1];

        if ($qty >= 1) {
            return $unitName;
        }

        $purchaseUnit = $matches[2];
        $base = static::isBase()->where('purchase_unit', $purchaseUnit)->value('name');

        if (! is_string($base) || $base === '' || strcasecmp($base, $purchaseUnit) === 0) {
            $lower = strtolower($purchaseUnit);
            $base = match (true) {
                $lower === 'kg' => 'g',
                $lower === 'litre', $lower === 'l' => 'ml',
                default => null,
            };

            if ($base === null) {
                return $unitName;
            }
        }

        $converted = $qty * (static::factorFor($purchaseUnit) ?? 1.0);

        return (rtrim(rtrim(number_format($converted, 3, '.', ''), '0'), '.') ?: '0').' '.$base;
    }

    /**
     * Whether a stored base quantity is plausible for the given unit and qty.
     * Catches pre-seed corruption (kg rows stored unconverted) without false
     * positives: correct rows match to rounding, corrupt ones differ ~1000x.
     */
    public static function baseQtyPlausible(string $unit, float $qty, float $baseQty): bool
    {
        $factor = static::factorFor($unit);

        if ($factor === null) {
            return true;
        }

        $expected = round($qty * $factor, 3);

        return abs($baseQty - $expected) <= max(0.05, abs($expected) * 0.01);
    }

    /**
     * Trusted base quantity for a stored item row: the stored value when
     * plausible, otherwise a fresh recomputation (self-healing for
     * pre-seed corruption). Keeps deletes always succeeding.
     */
    public static function storedBaseQty(string $unit, float $qty, float $stored): float
    {
        if ($stored > 0 && static::baseQtyPlausible($unit, $qty, $stored)) {
            return $stored;
        }

        if ($stored > 0) {
            return round(static::toBaseQty($unit, $qty), 3);
        }

        return $qty;
    }

    /**
     * Convert a purchase-unit quantity to base units, seed-proof via factorFor().
     */
    public static function toBaseQty(string $unitName, float $quantity): float
    {
        $unit = static::where('name', $unitName)->first();

        if ($unit) {
            return round($unit->toBase($quantity), 3);
        }

        $factor = static::factorFor($unitName);

        return round($factor ? $quantity * $factor : $quantity, 3);
    }

    public function toBase(float $quantity): float
    {
        return $quantity * (float) $this->to_base_factor;
    }

    public function fromBase(float $baseQuantity): float
    {
        $factor = (float) $this->to_base_factor;

        return $factor > 0 ? $baseQuantity / $factor : $baseQuantity;
    }

    /**
     * @return Collection<int, Unit>
     */
    public function relatedUnits(): Collection
    {
        return Unit::where('base_unit', $this->base_unit)->ordered()->get();
    }
}
