<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.store')]
class Show extends Component
{
    #[Locked]
    public Order $order;

    public bool $showCancelForm = false;

    public string $cancelReason = '';

    public function mount(): void
    {
        abort_unless($this->order->user_id === auth('web')->id(), 403);

        $this->order->load(['items.product', 'items.basket.products', 'user']);
    }

    public function cancelOrder(): void
    {
        $this->authorize('cancel', $this->order);

        $this->validate(['cancelReason' => ['required', 'string', 'max:200']]);

        app(OrderService::class)->cancel($this->order, $this->cancelReason, 'customer');

        $this->order->refresh();
    }

    public function payOnline(): void
    {
        if ($this->order->payment_method !== 'online' || $this->order->payment_status === 'paid') {
            return;
        }

        $razorpayOrderId = $this->order->payment_reference;

        if (! is_string($razorpayOrderId) || $razorpayOrderId === '') {
            $razorpayOrderId = app(RazorpayService::class)->createOrder(
                $this->order->total * 100,
                $this->order->order_number,
                ['order_id' => (string) $this->order->id],
            );
            $this->order->update(['payment_reference' => $razorpayOrderId]);
        }

        $this->dispatch('razorpay-open',
            key_id: config('razorpay.key_id'),
            order_id: $razorpayOrderId,
            amount: $this->order->total * 100,
            name: config('razorpay.name'),
            description: config('razorpay.description').' '.$this->order->order_number,
            theme_color: config('razorpay.theme_color'),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cancelReason.required' => 'Please tell us why you are cancelling this order.',
        ];
    }

    public function render(): View
    {
        return view('livewire.orders.show');
    }
}
