<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * @return array{revenue: int, orders: int, customers: int, products: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'revenue' => (int) Order::where('status', '!=', 'cancelled')->sum('total'),
            'orders' => Order::count(),
            'customers' => User::count(),
            'products' => Product::count(),
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    #[Computed]
    public function recentOrders(): Collection
    {
        return Order::with('user')
            ->latest()
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function pendingOrdersCount(): int
    {
        return Order::where('status', 'pending')->count();
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function lowStockProducts(): Collection
    {
        return Product::with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(6)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
