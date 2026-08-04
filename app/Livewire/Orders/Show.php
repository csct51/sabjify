<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.store')]
class Show extends Component
{
    #[Locked]
    public Order $order;

    public function mount(): void
    {
        abort_unless($this->order->user_id === auth('web')->id(), 403);

        $this->order->load(['items.product', 'user']);
    }

    public function cancelOrder(): void
    {
        $this->authorize('cancel', $this->order);

        app(OrderService::class)->cancel($this->order);

        $this->order->refresh();
    }

    public function render(): View
    {
        return view('livewire.orders.show');
    }
}
