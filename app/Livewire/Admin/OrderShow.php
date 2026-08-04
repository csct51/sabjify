<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrderShow extends Component
{
    #[Locked]
    public Order $order;

    public string $status = '';

    public string $paymentStatus = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->order->load(['items.product', 'user']);

        $this->status = $this->order->status;
        $this->paymentStatus = $this->order->payment_status;
    }

    public function updateStatus(): void
    {
        $this->validate(['status' => ['required', 'in:'.implode(',', array_keys(Order::STATUSES))]]);

        $data = ['status' => $this->status];

        if ($this->status === Order::STATUS_DELIVERED) {
            $data['delivered_at'] = now();
        }

        if ($this->status === Order::STATUS_CANCELLED) {
            $data['cancelled_at'] = now();
            $this->restockItems();
        }

        $this->order->update($data);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Order status updated.');
    }

    public function updatePaymentStatus(): void
    {
        $this->validate(['paymentStatus' => ['required', 'in:pending,paid,refunded']]);

        $this->order->update(['payment_status' => $this->paymentStatus]);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Payment status updated.');
    }

    public function cancelOrder(): void
    {
        app(OrderService::class)->cancel($this->order);

        $this->status = $this->order->status;
        $this->order->refresh();
    }

    public function render(): View
    {
        return view('livewire.admin.order-show');
    }

    private function restockItems(): void
    {
        foreach ($this->order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }
}
