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

    public function filter(string $status): void
    {
        $this->status = $status === 'all' ? null : $status;

        $this->resetPage();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function counts(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'all' => Order::count(),
            ...$counts,
        ];
    }

    public function render(): View
    {
        $orders = Order::with('user')->withCount('items')
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.orders', ['orders' => $orders]);
    }
}
