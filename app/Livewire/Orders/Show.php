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

    public bool $showCancelForm = false;

    public string $cancelReason = '';

    public function mount(): void
    {
        abort_unless($this->order->user_id === auth('web')->id(), 403);

        $this->order->load(['items.product', 'user']);
    }

    public function cancelOrder(): void
    {
        $this->authorize('cancel', $this->order);

        $this->validate(['cancelReason' => ['required', 'string', 'max:200']]);

        app(OrderService::class)->cancel($this->order, $this->cancelReason, 'customer');

        $this->order->refresh();
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
