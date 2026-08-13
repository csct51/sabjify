<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class RecipeForm extends Component
{
    use WithFileUploads;

    public ?Recipe $recipe = null;

    public string $title = '';

    public string $slug = '';

    public string $description = '';

    /** @var array<int, int> */
    public array $productIds = [];

    /** @var array<int, int|null> */
    public array $productUnitIds = [];

    public string $productSearch = '';

    public string $is_active = '1';

    public int $sort_order = 0;

    public ?TemporaryUploadedFile $image = null;

    public string $imageUrl = '';

    public bool $slugManuallyEdited = false;

    public function mount(?Recipe $recipe = null): void
    {
        $this->recipe = $recipe;

        if ($recipe) {
            $this->title = $recipe->title;
            $this->slug = $recipe->slug;
            $this->description = $recipe->description ?? '';
            $this->productIds = $recipe->products()->pluck('products.id')->all();

            foreach ($recipe->products()->withPivot('product_unit_id')->get() as $product) {
                $this->productUnitIds[$product->id] = $product->pivot->product_unit_id;
            }

            $this->is_active = $recipe->is_active ? '1' : '0';
            $this->sort_order = $recipe->sort_order;
            $this->imageUrl = $recipe->image && filter_var($recipe->image, FILTER_VALIDATE_URL) !== false ? $recipe->image : '';
        }
    }

    public function updatedTitle(): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
    }

    #[Computed]
    public function autoSlug(): string
    {
        return Str::slug($this->title);
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
    }

    public function save(): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($this->title);
        }

        $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'unique:recipes,slug,'.($this->recipe->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:1000'],
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['integer', 'exists:products,id'],
            'productUnitIds.*' => ['nullable', 'integer', 'exists:product_units,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'imageUrl' => ['nullable', 'url', 'max:500'],
        ]);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description !== '' ? $this->description : null,
            'is_active' => $this->is_active === '1',
            'sort_order' => $this->sort_order,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('recipes', 'public');
        } elseif ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        } elseif (! $this->recipe) {
            $data['image'] = null;
        }

        $sync = [];

        foreach ($this->productIds as $productId) {
            $sync[$productId] = [
                'product_unit_id' => $this->productUnitIds[$productId] ?? null,
            ];
        }

        if ($this->recipe) {
            $this->recipe->update($data);
            $this->recipe->products()->sync($sync);
            session()->flash('success', 'Recipe updated.');
        } else {
            $recipe = Recipe::create($data);
            $recipe->products()->sync($sync);
            session()->flash('success', 'Recipe created.');
        }

        $this->redirect(route('admin.recipes.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.recipe-form');
    }
}
