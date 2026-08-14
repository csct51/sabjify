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

    public string $cancelReason = '';

    public function mount(): void
    {
        abort_unless(auth('admin')->check(), 403);

        $this->order->load(['items.product', 'items.basket.products.units', 'user']);

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

        $this->order->update($data);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Order status updated.');
    }

    public function updatePaymentStatus(): void
    {
        abort_unless($this->order->payment_method === 'cod', 403);

        $this->validate(['paymentStatus' => ['required', 'in:pending,paid,refunded']]);

        $this->order->update(['payment_status' => $this->paymentStatus]);
        $this->order->refresh();

        $this->dispatch('toast', message: 'Payment status updated.');
    }

    public function cancelOrder(): void
    {
        $this->validate(['cancelReason' => ['required', 'string', 'max:200']]);

        app(OrderService::class)->cancel($this->order, $this->cancelReason, 'platform');

        $this->status = $this->order->status;
        $this->order->refresh();

        $this->dispatch('toast', message: 'Order cancelled.');
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cancelReason.required' => 'Please provide a reason for cancelling this order.',
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.order-show');
    }
}
