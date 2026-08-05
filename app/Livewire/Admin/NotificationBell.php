<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
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
    public function pendingOrdersCount(): int
    {
        return Order::where('status', Order::STATUS_PENDING)->count();
    }

    /**
     * @return Collection<int, Order>
     */
    #[Computed]
    public function pendingOrders(): Collection
    {
        return Order::with('user')
            ->where('status', Order::STATUS_PENDING)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.notification-bell');
    }
}
