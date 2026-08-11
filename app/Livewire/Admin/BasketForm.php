<?php

namespace App\Livewire\Admin;

use App\Models\Basket;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
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

    public string $is_active = '1';

    public int $sort_order = 0;

    /** @var array<int, int> */
    public array $productIds = [];

    /** @var array<int, string> */
    public array $units = [];

    /** @var array<int, int|string|null> */
    public array $prices = [];

    public string $productSearch = '';

    public ?TemporaryUploadedFile $image = null;

    public string $imageUrl = '';

    public bool $slugManuallyEdited = false;

    public function mount(?Basket $basket = null): void
    {
        $this->basket = $basket;

        if ($basket) {
            $this->name = $basket->name;
            $this->slug = $basket->slug;
            $this->type = $basket->type;
            $this->description = $basket->description ?? '';
            $this->price = $basket->price;
            $this->is_active = $basket->is_active ? '1' : '0';
            $this->sort_order = $basket->sort_order;
            $this->imageUrl = $basket->image && filter_var($basket->image, FILTER_VALIDATE_URL) !== false ? $basket->image : '';

            foreach ($basket->products()->withPivot('unit', 'price')->get() as $product) {
                $this->productIds[] = $product->id;
                $this->units[$product->id] = $product->pivot->unit;
                $this->prices[$product->id] = $product->pivot->price;
            }
        }
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
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function removeProduct(int $productId): void
    {
        $this->productIds = array_values(array_filter(
            $this->productIds,
            fn (int $id) => $id !== $productId
        ));

        unset($this->units[$productId], $this->prices[$productId]);
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
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['integer', 'exists:products,id'],
            'units.*' => ['nullable', 'string', 'max:40'],
            'prices.*' => ['nullable', 'integer', 'min:0'],
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

        foreach ($this->productIds as $productId) {
            $sync[$productId] = [
                'unit' => ! empty($this->units[$productId]) ? $this->units[$productId] : null,
                'price' => isset($this->prices[$productId]) && $this->prices[$productId] !== '' ? (int) $this->prices[$productId] : null,
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
