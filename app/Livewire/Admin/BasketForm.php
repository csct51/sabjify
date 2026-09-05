<?php

namespace App\Livewire\Admin;

use App\Models\Basket;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class BasketForm extends Component
{
    use WithFileUploads;

    public ?Basket $basket = null;

    public string $name = '';

    public string $slug = '';

    public string $type = Basket::TYPE_WELLNESS;

    public string $description = '';

    public int $price = 0;

    public ?int $mrp = null;

    public string $is_active = '1';

    public int $sort_order = 0;

    /** @var array<int, int> */
    public array $productIds = [];

    /** @var array<int, int|null> */
    public array $productUnitIds = [];

    /** @var array<int, string|null> */
    public array $productCustomQtys = [];

    /** @var array<int, int|null> */
    public array $productCustomPrices = [];

    public string $productSearch = '';

    public ?TemporaryUploadedFile $image = null;

    public string $imageUrl = '';

    public bool $slugManuallyEdited = false;

    public bool $priceManuallyEdited = false;

    public function mount(?Basket $basket = null): void
    {
        $this->basket = $basket;

        if ($basket) {
            $this->name = $basket->name;
            $this->slug = $basket->slug;
            $this->type = $basket->type;
            $this->description = $basket->description ?? '';
            $this->price = $basket->price;
            $this->mrp = $basket->mrp;
            $this->is_active = $basket->is_active ? '1' : '0';
            $this->sort_order = $basket->sort_order;
            $this->imageUrl = $basket->image && filter_var($basket->image, FILTER_VALIDATE_URL) !== false ? $basket->image : '';
            $this->priceManuallyEdited = true;

            foreach ($basket->products()->with(['units'])->withPivot('product_unit_id', 'unit', 'price')->get() as $product) {
                $this->productIds[] = $product->id;

                $unitId = $product->pivot->product_unit_id ?? $this->defaultUnitIdFor($product);
                $unit = $unitId ? $product->units->firstWhere('id', $unitId) : $product->units->first();

                $this->productUnitIds[$product->id] = $unitId;
                // Only genuine overrides hydrate the custom fields; values matching
                // the resolved unit/price stay blank so the dropdown keeps working.
                $this->productCustomQtys[$product->id] = $this->customQtyFromPivot($product, $product->pivot->unit, $unit?->unit);
                $this->productCustomPrices[$product->id] = $product->pivot->price !== null && (int) $product->pivot->price !== (int) ($unit?->price ?? $product->price)
                    ? $product->pivot->price
                    : null;
            }
        }
    }

    public function updatedProductIds(): void
    {
        $selected = $this->selectedProducts()->keyBy('id');

        foreach ($this->productIds as $productId) {
            if (! array_key_exists($productId, $this->productUnitIds) && isset($selected[$productId])) {
                $this->productUnitIds[$productId] = $this->defaultUnitIdFor($selected[$productId]);
            }
        }

        if (! $this->priceManuallyEdited) {
            $this->price = $this->calculatedPrice;
        }
    }

    private function defaultUnitIdFor(Product $product): ?int
    {
        return $this->basketUnitsFor($product, false)->first()?->id ?? $product->units->first()?->id;
    }

    /**
     * Units selectable for a basket line: only the product's own base.
     * The currently selected id is always included so legacy rows keep
     * rendering (saving remaps them — see save()).
     *
     * @return Collection<int, ProductUnit>
     */
    public function basketUnitsFor(Product $product, bool $includeSelected = true): Collection
    {
        $base = $product->baseUnit();
        $selectedId = $this->productUnitIds[$product->id] ?? null;

        return $product->units->filter(function ($unit) use ($base, $selectedId, $includeSelected) {
            if ($includeSelected && $selectedId !== null && (int) $unit->id === (int) $selectedId) {
                return true;
            }

            if ($base === null) {
                return true;
            }

            return Unit::where('name', $unit->unit)->value('base_unit') === $base;
        })->values();
    }

    /**
     * Extract the custom qty number from a stored pivot unit ("0.5 kg" → "0.5",
     * "500 g" → "0.5"). Returns null for blank, resolved-default, or legacy
     * free-text values.
     */
    private function customQtyFromPivot(Product $product, ?string $pivotUnit, ?string $resolvedUnit): ?string
    {
        if ($pivotUnit === null || trim($pivotUnit) === '' || $pivotUnit === $resolvedUnit) {
            return null;
        }

        $purchaseUnit = $product->purchaseUnit();
        $name = trim($pivotUnit);

        if (preg_match('/^(\d+(?:\.\d+)?)\s+'.preg_quote($purchaseUnit, '/').'$/i', $name, $matches)) {
            return rtrim(rtrim(number_format((float) $matches[1], 3, '.', ''), '0'), '.') ?: '0';
        }

        return null;
    }

    /**
     * Compose the stored pivot unit for a custom qty ("0.5 kg", "2 kg").
     */
    private function composeCustomUnit(Product $product, float $qty): string
    {
        $purchaseUnit = $product->purchaseUnit();
        $display = rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.') ?: '0';

        return "{$display} {$purchaseUnit}";
    }

    public function updatedProductUnitIds(): void
    {
        if (! $this->priceManuallyEdited) {
            $this->price = $this->calculatedPrice;
        }
    }

    public function updatedProductCustomPrices(): void
    {
        if (! $this->priceManuallyEdited) {
            $this->price = $this->calculatedPrice;
        }
    }

    public function updatedPrice(): void
    {
        $this->priceManuallyEdited = true;
    }

    public function applyCalculatedPrice(): void
    {
        $this->price = $this->calculatedPrice;
        $this->priceManuallyEdited = false;
    }

    public function updatedName(): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
    }

    #[Computed]
    public function autoSlug(): string
    {
        return Str::slug($this->name);
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return Product::active()
            ->with('category')
            ->when($this->productSearch !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->productSearch.'%')
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', '%'.$this->productSearch.'%'));
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function selectedProducts(): Collection
    {
        if ($this->productIds === []) {
            return new Collection;
        }

        return Product::active()
            ->whereIn('id', $this->productIds)
            ->with('category', 'units')
            ->orderBy('name')
            ->get();
    }

    public function removeProduct(int $productId): void
    {
        $this->productIds = array_values(array_filter(
            $this->productIds,
            fn (int $id) => $id !== $productId
        ));

        unset($this->productUnitIds[$productId]);
        unset($this->productCustomQtys[$productId]);
        unset($this->productCustomPrices[$productId]);
    }

    #[Computed]
    public function calculatedPrice(): int
    {
        $total = 0;

        foreach ($this->selectedProducts() as $product) {
            $customPrice = $this->productCustomPrices[$product->id] ?? null;

            if ($customPrice !== null && $customPrice !== '') {
                $total += (int) $customPrice;

                continue;
            }

            $unitId = $this->productUnitIds[$product->id] ?? null;

            $unit = $unitId
                ? $product->units->firstWhere('id', $unitId)
                : $product->units->first();

            $total += $unit?->price ?? $product->price;
        }

        return $total;
    }

    public function save(): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($this->name);
        }

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'unique:baskets,slug,'.($this->basket->id ?? 'NULL')],
            'type' => ['required', 'in:'.implode(',', array_keys(Basket::TYPES))],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:1'],
            'mrp' => ['nullable', 'integer', 'min:1'],
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['integer', 'exists:products,id'],
            'productUnitIds.*' => ['nullable', 'integer', 'exists:product_units,id'],
            'productCustomQtys.*' => ['nullable', 'numeric', 'min:0.001', 'max:99999999'],
            'productCustomPrices.*' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
        ]);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description !== '' ? $this->description : null,
            'price' => $this->price,
            'mrp' => $this->mrp === null || $this->mrp === '' ? null : (int) $this->mrp,
            'is_active' => $this->is_active === '1',
            'sort_order' => $this->sort_order,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('baskets', 'public');
        } elseif ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        } elseif (! $this->basket) {
            $data['image'] = null;
        }

        $sync = [];

        $selected = $this->selectedProducts()->keyBy('id');

        foreach ($this->productIds as $productId) {
            $product = $selected[$productId];

            $customQty = $this->productCustomQtys[$productId] ?? null;
            $customQty = ($customQty !== null && $customQty !== '') ? (float) $customQty : null;

            if ($customQty !== null && Unit::integerOnlyFor($product->purchaseUnit()) && floor($customQty) != $customQty) {
                throw ValidationException::withMessages([
                    "productCustomQtys.{$productId}" => 'Custom qty must be a whole number for '.$product->purchaseUnit().'.',
                ]);
            }

            $customPrice = $this->productCustomPrices[$productId] ?? null;
            $customPrice = $customPrice !== null && $customPrice !== '' ? (int) $customPrice : null;

            if ($customQty !== null) {
                $sync[$productId] = [
                    'product_unit_id' => null,
                    'unit' => $this->composeCustomUnit($product, $customQty),
                    'price' => $customPrice ?? $product->units->first()?->price ?? $product->price,
                ];

                continue;
            }

            $unitId = $this->productUnitIds[$productId] ?? null;

            if ($unitId !== null) {
                $allowed = $this->basketUnitsFor($product, false)->pluck('id')->map(fn ($id) => (int) $id)->all();

                if (! in_array((int) $unitId, $allowed, true)) {
                    throw ValidationException::withMessages([
                        "productUnitIds.{$productId}" => 'Unit must share the product base unit.',
                    ]);
                }
            }

            $unit = $unitId
                ? $product->units->firstWhere('id', $unitId)
                : $product->units->first();

            $sync[$productId] = [
                'product_unit_id' => $unitId,
                'unit' => $unit?->unit ?? $product->unit,
                'price' => $customPrice ?? $unit?->price ?? $product->price,
            ];
        }

        if ($this->basket) {
            $this->basket->update($data);
            $this->basket->products()->sync($sync);
            session()->flash('success', 'Basket updated.');
        } else {
            $basket = Basket::create($data);
            $basket->products()->sync($sync);
            session()->flash('success', 'Basket created.');
        }

        $this->redirect(route('admin.baskets.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.basket-form');
    }
}
