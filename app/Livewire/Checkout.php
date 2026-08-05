<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\CartItem;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Checkout')]
class Checkout extends Component
{
    public string $addressMode = 'existing';

    public ?int $addressId = null;

    public string $paymentMethod = 'cod';

    public string $notes = '';

    public bool $saveAddress = true;

    public string $label = 'Home';

    public string $receiverName = '';

    public string $receiverPhone = '';

    public string $addressLine = '';

    public string $landmark = '';

    public string $city = '';

    public string $state = '';

    public string $pincode = '';

    public function mount(): void
    {
        if (! in_array($this->paymentMethod, $this->enabledPaymentMethods(), true)) {
            $this->paymentMethod = $this->enabledPaymentMethods()[0];
        }

        $this->receiverPhone = auth('web')->user()->phone;

        $default = $this->addresses()->firstWhere('is_default', true) ?? $this->addresses()->first();

        if ($default) {
            $this->addressId = $default->id;
            $this->label = $default->label;
            $this->receiverName = $default->receiver_name;
            $this->receiverPhone = $default->receiver_phone;
            $this->addressLine = $default->address_line;
            $this->landmark = $default->landmark ?? '';
            $this->city = $default->city;
            $this->state = $default->state;
            $this->pincode = $default->pincode;
        } else {
            $this->addressMode = 'new';
            $this->receiverName = auth('web')->user()->name;
        }
    }

    /**
     * @return Collection<int, CartItem>
     */
    #[Computed]
    public function cartItems(): Collection
    {
        return auth('web')->user()->cartItems()->with('product.category')->get();
    }

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        return auth('web')->user()->addresses()->latest()->get();
    }

    #[Computed]
    public function subtotal(): int
    {
        return $this->cartItems()->sum(fn (CartItem $item) => $item->product ? $item->product->price * $item->quantity : 0);
    }

    #[Computed]
    public function deliveryFee(): int
    {
        $subtotal = $this->subtotal();

        if ($subtotal === 0) {
            return 0;
        }

        return $subtotal >= config('mart.free_delivery_threshold') ? 0 : (int) config('mart.delivery_fee');
    }

    #[Computed]
    public function total(): int
    {
        return $this->subtotal() + $this->deliveryFee();
    }

    public function selectAddress(Address $address): void
    {
        $this->addressId = $address->id;
        $this->addressMode = 'existing';
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function enabledPaymentMethods(): array
    {
        return config('mart.enabled_payment_methods', ['cod', 'online']);
    }

    public function placeOrder(): void
    {
        if ($this->cartItems()->isEmpty()) {
            $this->redirect(route('shop'));

            return;
        }

        $this->validate([
            'paymentMethod' => ['required', 'in:'.implode(',', $this->enabledPaymentMethods())],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->addressMode === 'new' || ! $this->addressId) {
            $this->validate([
                'label' => ['required', 'string', 'max:20'],
                'receiverName' => ['required', 'string', 'max:100'],
                'receiverPhone' => ['required', 'regex:/^[6-9]\d{9}$/'],
                'addressLine' => ['required', 'string', 'max:255'],
                'landmark' => ['nullable', 'string', 'max:100'],
                'city' => ['required', 'string', 'max:100'],
                'state' => ['required', 'string', 'max:100'],
                'pincode' => ['required', 'digits:6'],
            ]);

            if ($this->saveAddress) {
                auth('web')->user()->addresses()->create([
                    'label' => $this->label,
                    'receiver_name' => $this->receiverName,
                    'receiver_phone' => $this->receiverPhone,
                    'address_line' => $this->addressLine,
                    'landmark' => $this->landmark ?: null,
                    'city' => $this->city,
                    'state' => $this->state,
                    'pincode' => $this->pincode,
                    'is_default' => ! $this->addresses()->contains('is_default', true),
                ]);
            }

            $data = [
                'receiver_name' => $this->receiverName,
                'receiver_phone' => $this->receiverPhone,
                'address_line' => $this->addressLine,
                'city' => $this->city,
                'state' => $this->state,
                'pincode' => $this->pincode,
                'label' => $this->label,
                'notes' => $this->notes ?: null,
            ];
        } else {
            $address = $this->addresses()->firstWhere('id', $this->addressId);

            $data = [
                'receiver_name' => $address->receiver_name,
                'receiver_phone' => $address->receiver_phone,
                'address_line' => $address->address_line,
                'city' => $address->city,
                'state' => $address->state,
                'pincode' => $address->pincode,
                'label' => $address->label,
                'notes' => $this->notes ?: null,
            ];
        }

        $data['payment_method'] = $this->paymentMethod;

        $order = app(OrderService::class)->createFromCart(auth('web')->user(), $data);

        $this->dispatch('cart-updated');

        $this->redirect(route('orders.show', $order), navigate: true);
    }

    public function render(): View
    {
        if ($this->cartItems()->isEmpty()) {
            $this->redirect(route('cart'));

            return view('livewire.checkout', ['cartEmpty' => true]);
        }

        return view('livewire.checkout');
    }
}
