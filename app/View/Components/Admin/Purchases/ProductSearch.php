<?php

namespace App\View\Components\Admin\Purchases;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

class ProductSearch extends Component
{
    public Collection $products;

    public function __construct(
        public string $target = 'formProductId',
        public string $search = 'formProductSearch',
        public ?int $productId = null,
        public string $placeholder = 'Search product...',
    ) {
        $this->products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.purchases.product-search');
    }
}
