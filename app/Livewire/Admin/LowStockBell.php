<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LowStockBell extends Component
{
    public bool $show = false;

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function close(): void
    {
        $this->show = false;
    }

    #[Computed]
    public function lowStockCount(): int
    {
        return Product::lowStock()->count();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function lowStockProducts(): Collection
    {
        return Product::lowStock()
            ->with('category')
            ->orderBy('current_stock')
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.low-stock-bell');
    }
}
