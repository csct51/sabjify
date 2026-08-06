<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-stone-900 mb-6">Checkout</h1>

        @if (! empty($cartEmpty))
            <div class="bg-white rounded-2xl border border-stone-200 text-center py-20">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-cart" class="w-8 h-8"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">Your cart is empty</h2>
                <a href="{{ route('shop') }}" wire:navigate class="mt-5 inline-block rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition">Start Shopping</a>
            </div>
        @else
            <div class="grid lg:grid-cols-[1fr_360px] gap-8 items-start">
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-stone-200 p-6">
                        <h2 class="font-semibold text-stone-900 mb-4">Delivery Address</h2>

                        @if ($this->addresses->isNotEmpty())
                            <div class="space-y-2 mb-4">
                                @foreach ($this->addresses as $address)
                                    <button
                                        type="button"
                                        wire:click="selectAddress({{ $address->id }})"
                                        class="w-full text-left rounded-xl border p-4 transition {{ $this->addressMode === 'existing' && $this->addressId === $address->id ? 'border-brand-500 bg-brand-50' : 'border-stone-200 hover:border-stone-300' }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-sm text-stone-900 flex items-center gap-2">
                                                <span class="rounded-md bg-white border border-stone-200 px-2 py-0.5 text-xs text-stone-500">{{ $address->label }}</span>
                                                @if ($address->is_default)
                                                    <span class="text-xs text-brand-600 font-medium">Default</span>
                                                @endif
                                            </span>
                                            <span class="w-4 h-4 rounded-full border-2 {{ $this->addressMode === 'existing' && $this->addressId === $address->id ? 'border-brand-600 bg-brand-600' : 'border-stone-300' }}"></span>
                                        </div>
                                        <p class="mt-2 text-sm text-stone-600">{{ $address->receiver_name }} · {{ $address->receiver_phone }}</p>
                                        <p class="mt-1 text-sm text-stone-500">{{ $address->address_line }}, {{ $address->landmark ? $address->landmark.', ' : '' }}{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                    </button>
                                @endforeach
                            </div>

                            <button type="button" wire:click="$set('addressMode', 'new')" class="text-sm font-semibold text-brand-600 hover:text-brand-700 {{ $this->addressMode === 'new' ? 'underline' : '' }}">
                                + Add new address
                            </button>
                        @endif

                        @if ($this->addressMode === 'new')
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Label <span class="text-red-500">*</span></label>
                                    <select wire:model="label" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                                        <option value="Home">Home</option>
                                        <option value="Work">Work</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Receiver Name <span class="text-red-500">*</span></label>
                                    <input wire:model="receiverName" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('receiverName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                    <input wire:model="receiverPhone" type="tel" maxlength="10" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('receiverPhone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Address <span class="text-red-500">*</span></label>
                                    <textarea wire:model="addressLine" rows="2" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                                    @error('addressLine')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Landmark <span class="text-stone-400">(optional)</span></label>
                                    <input wire:model="landmark" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">City <span class="text-red-500">*</span></label>
                                    <input wire:model="city" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1">State <span class="text-red-500">*</span></label>
                                    <input wire:model="state" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('state')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-stone-700 mb-1">Pincode <span class="text-red-500">*</span></label>
                                    <input wire:model="pincode" type="text" maxlength="6" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('pincode')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <label class="col-span-2 flex items-center gap-2 text-sm text-stone-600">
                                    <input wire:model="saveAddress" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                    Save this address for future orders
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl border border-stone-200 p-6">
                        <h2 class="font-semibold text-stone-900 mb-4">Payment Method <span class="text-red-500">*</span></h2>
                        <div class="space-y-2">
                            @foreach (config('mart.payment_methods') as $key => $method)
                                @if (in_array($key, $this->enabledPaymentMethods, true))
                                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                        <input type="radio" wire:model.live="paymentMethod" value="{{ $key }}" class="rounded-full border-stone-300 text-brand-600 focus:ring-brand-500">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="{{ $method['icon'] }}" class="w-5 h-5"></i></span>
                                        <span>
                                            <span class="block text-sm font-medium text-stone-900">{{ $method['label'] }}</span>
                                            <span class="block text-xs text-stone-500">{{ $method['description'] }}</span>
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @error('paymentMethod')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="bg-white rounded-2xl border border-stone-200 p-6">
                        <h2 class="font-semibold text-stone-900 mb-3">Order Notes <span class="text-stone-400 font-normal text-xs">(optional)</span></h2>
                        <textarea wire:model="notes" rows="2" placeholder="e.g. Call me before delivery" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-stone-200 p-6 lg:sticky lg:top-20">
                    <h2 class="font-semibold text-stone-900 mb-4">Order Summary</h2>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-1 mb-4">
                        @foreach ($this->cartItems as $item)
                            <div class="flex items-center justify-between gap-3 text-sm" wire:key="co-{{ $item->id }}">
                                <span class="flex items-center gap-2 text-stone-600 min-w-0">
                                    <span class="text-xs text-stone-400">×{{ $item->quantity }}</span>
                                    <span class="truncate">{{ $item->product->name }}</span>
                                </span>
                                <span class="font-medium text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($item->product->price * $item->quantity, 'INR') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-stone-100 pt-3 space-y-3 text-sm">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($this->subtotal, 'INR') }}</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>Delivery fee</span>
                            @if ($this->deliveryFee === 0)
                                <span class="font-medium text-green-600">FREE</span>
                            @else
                                <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($this->deliveryFee, 'INR') }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-stone-900">Total</span>
                            <span class="font-bold text-lg text-stone-900">{{ \Illuminate\Support\Number::currency($this->total, 'INR') }}</span>
                        </div>
                    </div>

                    <button type="button" wire:click="placeOrder" class="mt-5 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition">
                        {{ $this->paymentMethod === 'online' ? 'Pay Securely' : 'Place Order' }} · {{ \Illuminate\Support\Number::currency($this->total, 'INR') }}
                    </button>

                    @error('minimum')
                        <p class="mt-2 text-xs text-red-600 text-center">{{ $message }}</p>
                    @enderror

                    @if ($this->paymentMethod === 'online')
                        <p class="mt-2 text-xs text-stone-400 text-center">You will be redirected to Razorpay to complete the payment. Your order is placed only after payment succeeds.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @assets
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endassets

    @script
        <script>
            Livewire.on('razorpay-open', (payload) => {
                openRazorpay(payload);
            });

            async function openRazorpay(payload) {
                if (typeof window.Razorpay !== 'function') {
                    await loadRazorpayScript();
                }

                if (typeof window.Razorpay !== 'function') {
                    alert('Razorpay failed to load. Please refresh and try again.');
                    return;
                }

                const options = {
                    key: payload.key_id,
                    amount: payload.amount,
                    currency: 'INR',
                    name: payload.name,
                    description: payload.description,
                    order_id: payload.order_id,
                    prefill: {
                        name: '{{ addslashes($this->receiverName) }}',
                        contact: '{{ $this->receiverPhone }}',
                    },
                    theme: { color: payload.theme_color },
                    handler: (response) => {
                        fetch('{{ route('checkout.payment.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            },
                            body: JSON.stringify(response),
                        })
                            .then((res) => res.json())
                            .then((data) => {
                                if (data.success) {
                                    window.location.href = data.redirect;
                                } else {
                                    alert(data.message || 'Payment could not be verified. Please try again.');
                                }
                            })
                            .catch(() => {
                                alert('Something went wrong while confirming your payment. Your cart is still saved. Please try again.');
                            });
                    },
                };

                try {
                    const razorpay = new window.Razorpay(options);
                    razorpay.open();
                } catch (error) {
                    console.error('Razorpay checkout failed', error);
                    alert('The payment window could not be opened. Please try again.');
                }
            }

            function loadRazorpayScript() {
                return new Promise((resolve) => {
                    const existing = document.querySelector('script[src="https://checkout.razorpay.com/v1/checkout.js"]');

                    if (existing) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
                    script.onload = () => resolve();
                    script.onerror = () => resolve();
                    document.head.appendChild(script);
                });
            }
        </script>
    @endscript
</div>
