<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public ?int $categoryId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $in_stock = true;

    public string $is_active = '1';

    public bool $is_featured = false;

    public int $sort_order = 0;

    /** @var array<int, array{unit: string, price: string, mrp: string|null}> */
    public array $unitRows = [];

    public ?TemporaryUploadedFile $image = null;

    public string $imageUrl = '';

    public bool $slugManuallyEdited = false;

    public function mount(?Product $product = null): void
    {
        $this->product = $product;

        if ($product) {
            $this->categoryId = $product->category_id;
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->description = $product->description ?? '';
            $this->in_stock = $product->in_stock;
            $this->is_active = $product->is_active ? '1' : '0';
            $this->is_featured = $product->is_featured;
            $this->sort_order = $product->sort_order;
            $this->imageUrl = $product->image && filter_var($product->image, FILTER_VALIDATE_URL) !== false ? $product->image : '';

            foreach ($product->units as $unit) {
                $this->unitRows[] = [
                    'unit' => $unit->unit,
                    'price' => (string) $unit->price,
                    'mrp' => $unit->mrp !== null ? (string) $unit->mrp : null,
                ];
            }
        }

        if ($this->unitRows === []) {
            $this->addUnitRow();
        }
    }

    public function addUnitRow(): void
    {
        $this->unitRows[] = ['unit' => '', 'price' => '', 'mrp' => null];
    }

    public function removeUnitRow(int $index): void
    {
        unset($this->unitRows[$index]);

        $this->unitRows = array_values($this->unitRows);

        if ($this->unitRows === []) {
            $this->addUnitRow();
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
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Unit>
     */
    #[Computed]
    public function units(): Collection
    {
        return Unit::ordered()->get();
    }

    public function save(): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($this->name);
        }

        $this->validate([
            'categoryId' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'unique:products,slug,'.($this->product->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:1000'],
            'in_stock' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'unitRows' => ['required', 'array', 'min:1'],
            'unitRows.*.unit' => ['required', 'string', 'max:20'],
            'unitRows.*.price' => ['required', 'integer', 'min:1'],
            'unitRows.*.mrp' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
        ]);

        $first = $this->unitRows[0];

        $data = [
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'unit' => $first['unit'],
            'price' => (int) $first['price'],
            'mrp' => $first['mrp'] !== null && $first['mrp'] !== '' ? (int) $first['mrp'] : null,
            'in_stock' => $this->in_stock,
            'is_active' => $this->is_active === '1',
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        } elseif ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        } elseif (! $this->product) {
            $data['image'] = null;
        }

        if ($this->product) {
            $this->product->update($data);
            $product = $this->product;
            session()->flash('success', 'Product updated.');
        } else {
            $product = Product::create($data);
            session()->flash('success', 'Product created.');
        }

        $this->syncUnits($product);

        $this->redirect(route('admin.products.index'), navigate: true);
    }

    private function syncUnits(Product $product): void
    {
        $product->units()->delete();

        foreach ($this->unitRows as $index => $row) {
            $product->units()->create([
                'unit' => $row['unit'],
                'price' => (int) $row['price'],
                'mrp' => $row['mrp'] !== null && $row['mrp'] !== '' ? (int) $row['mrp'] : null,
                'sort_order' => $index,
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.product-form');
    }
}
