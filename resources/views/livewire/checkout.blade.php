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
            @php
                $checkoutStep = $this->step;
            @endphp

            <ol class="flex items-center gap-2 sm:gap-4 mb-8 text-sm">
                @foreach (['Address' => 1, 'Payment' => 2, 'Review' => 3] as $label => $num)
                    @php
                        $state = $num < $checkoutStep ? 'done' : ($num === $checkoutStep ? 'current' : 'todo');
                    @endphp
                    <li class="flex items-center gap-2 {{ $state === 'done' ? 'text-brand-700' : ($state === 'current' ? 'text-stone-900 font-semibold' : 'text-stone-400') }}">
                        @if ($state === 'done')
                            <button type="button" wire:click="backToStep({{ $num }})" class="flex items-center gap-2 hover:opacity-80 transition">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold bg-brand-600 text-white">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span class="hidden sm:inline">{{ $label }}</span>
                            </button>
                        @else
                            <span class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $state === 'current' ? 'bg-brand-50 text-brand-700 ring-2 ring-brand-200' : 'bg-stone-100 text-stone-400' }}">
                                    {{ $num }}
                                </span>
                                <span class="hidden sm:inline">{{ $label }}</span>
                            </span>
                        @endif
                    </li>
                    @if ($num < 3)
                        <li class="flex-1 h-px {{ $num < $checkoutStep ? 'bg-brand-300' : 'bg-stone-200' }}"></li>
                    @endif
                @endforeach
            </ol>

            <div class="grid gap-4 lg:gap-8 items-start {{ $this->step === 3 ? 'lg:grid-cols-[1fr_360px]' : 'lg:grid-cols-1' }}">
                <div class="space-y-6">
                    @if ($this->step === 1)
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
                                            <p class="mt-1 text-sm text-stone-500">{{ $address->address_line }}{{ $address->landmark ? ', '.$address->landmark : '' }}</p>
                                        </button>
                                    @endforeach
                                </div>

                                <button type="button" wire:click="addNewAddress" class="text-sm font-semibold text-brand-600 hover:text-brand-700 {{ $this->addressMode === 'new' ? 'underline' : '' }}">
                                    + Add new address
                                </button>

                                @if ($this->addressMode === 'existing' && ($this->latitude === null || $this->longitude === null))
                                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                        <p class="text-sm text-amber-700 mb-2">This address needs a delivery location. Tap the map to set it.</p>
                                        <x-location-map
                                            :lat="$latitude"
                                            :lng="$longitude"
                                            lat-prop="latitude"
                                            lng-prop="longitude"
                                            :autofill="true"
                                            geolocate
                                        />
                                        @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif
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
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-stone-700 mb-2">Delivery Location</label>
                                        <x-location-map
                                            :lat="$latitude"
                                            :lng="$longitude"
                                            lat-prop="latitude"
                                            lng-prop="longitude"
                                            :autofill="true"
                                            geolocate
                                        />
                                        @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                        @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <label class="col-span-2 flex items-center gap-2 text-sm text-stone-600">
                                        <input wire:model="saveAddress" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                        Save this address for future orders
                                    </label>
                                </div>
                            @endif

                            @error('address')<p class="mt-3 text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('delivery')<p class="mt-3 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="button" wire:click="nextFromAddress" wire:loading.attr="disabled" wire:target="nextFromAddress" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition disabled:opacity-70">
                                <span wire:loading.remove wire:target="nextFromAddress">Continue to Payment</span>
                                <span wire:loading.inline-flex wire:target="nextFromAddress" class="inline-flex items-center gap-2"><x-loading-spinner class="w-4 h-4" /> Saving…</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @elseif ($this->step === 2)
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

                        <div class="flex items-center justify-between">
                            <button type="button" wire:click="backToAddress" class="inline-flex items-center gap-2 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold px-5 py-3 text-sm transition">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                            </button>
                            <button type="button" wire:click="nextFromPayment" wire:loading.attr="disabled" wire:target="nextFromPayment" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition disabled:opacity-70">
                                <span wire:loading.remove wire:target="nextFromPayment">Continue to Review</span>
                                <span wire:loading.inline-flex wire:target="nextFromPayment" class="inline-flex items-center gap-2"><x-loading-spinner class="w-4 h-4" /> Saving…</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-stone-200 p-6">
                            <h2 class="font-semibold text-stone-900 mb-4">Review your order</h2>

                            <div class="rounded-xl border border-stone-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-wide text-stone-400 font-medium">Deliver to</p>
                                        @if ($this->addressMode === 'existing' && $this->addressId)
                                            @php $selected = $this->addresses->firstWhere('id', $this->addressId); @endphp
                                            <p class="mt-1 text-sm font-medium text-stone-900">
                                                <span class="rounded-md bg-stone-100 px-2 py-0.5 text-xs text-stone-500 mr-1">{{ $selected->label }}</span>
                                                {{ $selected->receiver_name }} · {{ $selected->receiver_phone }}
                                            </p>
                                            <p class="mt-1 text-sm text-stone-500">{{ $selected->address_line }}{{ $selected->landmark ? ', '.$selected->landmark : '' }}</p>
                                        @else
                                            <p class="mt-1 text-sm font-medium text-stone-900">
                                                <span class="rounded-md bg-stone-100 px-2 py-0.5 text-xs text-stone-500 mr-1">{{ $this->label }}</span>
                                                {{ $this->receiverName }} · {{ $this->receiverPhone }}
                                            </p>
                                            <p class="mt-1 text-sm text-stone-500">{{ $this->addressLine }}{{ $this->landmark ? ', '.$this->landmark : '' }}</p>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="backToAddress" class="shrink-0 text-sm font-semibold text-brand-600 hover:text-brand-700">Edit</button>
                                </div>
                            </div>

                            <div class="rounded-xl border border-stone-200 p-4 mt-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-wide text-stone-400 font-medium">Payment</p>
                                        @php $pm = config('mart.payment_methods')[$this->paymentMethod]; @endphp
                                        <p class="mt-1 text-sm font-medium text-stone-900 flex items-center gap-2">
                                            <i data-lucide="{{ $pm['icon'] }}" class="w-4 h-4 text-brand-600"></i>
                                            {{ $pm['label'] }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-stone-500">{{ $pm['description'] }}</p>
                                    </div>
                                    <button type="button" wire:click="backToPayment" class="shrink-0 text-sm font-semibold text-brand-600 hover:text-brand-700">Edit</button>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="block text-sm font-medium text-stone-700 mb-1">Delivery Time <span class="text-red-500">*</span></p>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                        <input type="radio" wire:model.live="deliverySlot" value="morning" class="rounded-full border-stone-300 text-brand-600 focus:ring-brand-500">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="sun" class="w-5 h-5"></i></span>
                                        <span>
                                            <span class="block text-sm font-medium text-stone-900">Morning — 8 AM to 12 PM</span>
                                            <span class="block text-xs text-stone-500">Fresh delivery to start your day</span>
                                        </span>
                                    </label>
                                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                        <input type="radio" wire:model.live="deliverySlot" value="evening" class="rounded-full border-stone-300 text-brand-600 focus:ring-brand-500">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="moon" class="w-5 h-5"></i></span>
                                        <span>
                                            <span class="block text-sm font-medium text-stone-900">Evening — 6 PM to 9 PM</span>
                                            <span class="block text-xs text-stone-500">Evening delivery after work</span>
                                        </span>
                                    </label>
                                </div>
                                @error('deliverySlot')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-stone-700 mb-1">Order Notes <span class="text-stone-400 font-normal text-xs">(optional)</span></label>
                                <textarea wire:model="notes" rows="2" placeholder="e.g. Call me before delivery" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-start hidden lg:flex">
                            <button type="button" wire:click="backToPayment" class="inline-flex items-center gap-2 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold px-5 py-3 text-sm transition">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Payment
                            </button>
                        </div>
                    @endif
                </div>

                @if ($this->step === 3)
                <div class="bg-white rounded-2xl border border-stone-200 p-6 lg:sticky lg:top-20">
                    <h2 class="font-semibold text-stone-900 mb-4">Order Summary</h2>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-1 mb-4">
                        @foreach ($this->cartItems as $item)
                            <div class="flex items-center justify-between gap-3 text-sm" wire:key="co-{{ $item->id }}">
                                <span class="flex items-center gap-2 text-stone-600 min-w-0">
                                    <span class="text-xs text-stone-400">×{{ $item->quantity }}</span>
                                    <span class="truncate">{{ $item->name() }}</span>
                                    @if ($item->unitName())
                                        <span class="text-xs text-stone-400 shrink-0">{{ $item->unitName() }}</span>
                                    @endif
                                </span>
                                <span class="font-medium text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($item->unitPrice() * $item->quantity, 'INR') }}</span>
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

                    @if ($this->step === 3)
                        <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder" class="mt-5 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition disabled:opacity-70">
                            <span wire:loading.remove wire:target="placeOrder">
                                {{ $this->paymentMethod === 'online' ? 'Pay Securely' : 'Place Order' }} · {{ \Illuminate\Support\Number::currency($this->total, 'INR') }}
                            </span>
                            <span wire:loading.inline-flex wire:target="placeOrder" class="inline-flex items-center gap-2">
                                <x-loading-spinner class="w-4 h-4" />
                                {{ $this->paymentMethod === 'online' ? 'Processing Payment...' : 'Placing Order...' }}
                            </span>
                        </button>

                        @if ($this->paymentMethod === 'online')
                            <p class="mt-2 text-xs text-stone-400 text-center">You will be redirected to Razorpay to complete the payment. Your order is placed only after payment succeeds.</p>
                        @endif
                    @endif

                    @error('minimum')
                        <p class="mt-2 text-xs text-red-600 text-center">{{ $message }}</p>
                    @enderror

                    @error('stock')
                        <p class="mt-2 text-xs text-red-600 text-center">{{ $message }}</p>
                    @enderror

                    @error('delivery')
                        <p class="mt-2 text-xs text-red-600 text-center">{{ $message }}</p>
                    @enderror

                    <button type="button" wire:click="backToPayment" class="lg:hidden mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold py-3 text-sm transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Payment
                    </button>
                </div>
                @endif
            </div>
        @endif
    </div>

    @assets
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endassets

    @script
        <script>
            let razorpayInstance = null;

            Livewire.on('razorpay-open', (payload) => {
                // A stale instance trips Razorpay's internal "previous checkout
                // still alive" check ("browser not supported" alert), so always
                // tear down before opening a fresh one.
                destroyRazorpayInstance();
                openRazorpay(payload);
            });

            function destroyRazorpayInstance() {
                if (! razorpayInstance) {
                    return;
                }

                try {
                    if (typeof razorpayInstance.close === 'function') {
                        razorpayInstance.close();
                    }
                } catch (error) {
                    console.error('Razorpay teardown failed', error);
                }

                razorpayInstance = null;
            }

            async function openRazorpay(payload) {
                if (typeof window.Razorpay !== 'function') {
                    try {
                        await loadRazorpayScript();
                    } catch (error) {
                        alert('Razorpay failed to load. Please refresh and try again.');
                        return;
                    }
                }

                if (typeof window.Razorpay !== 'function') {
                    alert('Razorpay failed to load. Please refresh and try again.');
                    return;
                }

                if (! payload.key_id || ! payload.order_id) {
                    console.error('Razorpay misconfigured: missing key_id or order_id.');
                    alert('Online payment is not configured correctly. Please try cash on delivery or contact support.');
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
                    modal: {
                        ondismiss: () => {
                            razorpayInstance = null;
                        },
                    },
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
                    razorpayInstance = razorpay;

                    razorpay.on('modal:close', () => {
                        razorpayInstance = null;
                    });

                    razorpay.open();
                } catch (error) {
                    razorpayInstance = null;
                    console.error('Razorpay checkout failed', error);
                    alert('The payment window could not be opened. Please try again.');
                }
            }

            function loadRazorpayScript() {
                return new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[src="https://checkout.razorpay.com/v1/checkout.js"]');

                    if (existing) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Razorpay checkout.js failed to load.'));
                    document.head.appendChild(script);
                });
            }
        </script>
    @endscript
</div>
