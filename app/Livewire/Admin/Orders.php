<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Orders')]
class Orders extends Component
{
    use WithPagination;

    #[Url]
    public ?string $status = null;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return array{all: int, pending: int, delivered: int, cancelled: int}
     */
    #[Computed]
    public function counts(): array
    {
        return [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
    }

    public function render(): View
    {
        $orders = Order::with('user')->withCount('items')
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')->orWhere('phone', 'like', '%'.$this->search.'%'));
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.orders', ['orders' => $orders]);
    }
}
