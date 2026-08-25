<div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-stone-400 mb-6 flex items-center gap-1.5">
            <a href="{{ route('orders.index') }}" wire:navigate class="hover:text-brand-600">← My Orders</a>
        </nav>

        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-stone-500 mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$order->status" />
            </div>
        </div>

        @if ($order->status === 'delivered')
            <div class="rounded-2xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 mb-6 inline-flex items-center gap-2"><i data-lucide="circle-check" class="w-5 h-5"></i> Order delivered successfully. Enjoy your fresh produce!</div>
        @elseif ($order->status === 'cancelled')
            <div class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 mb-6">
                This order was cancelled by {{ $order->cancelled_by === 'platform' ? 'the store' : 'you' }}.
                @if ($order->cancelled_reason)
                    <span class="font-medium">Reason: {{ $order->cancelled_reason }}</span>
                @endif
            </div>
        @endif

        @if ($order->payment_method === 'online' && $order->payment_status === 'pending' && in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_CONFIRMED], true))
            <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm text-amber-700">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    <span>Your order is placed, but the payment is pending.</span>
                </div>
                <button type="button" wire:click="payOnline" wire:loading.attr="disabled" wire:target="payOnline" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-5 py-2 text-sm font-semibold transition disabled:opacity-70">
                    <span wire:loading.remove wire:target="payOnline">
                        Pay Now · {{ \Illuminate\Support\Number::currency($order->total, 'INR') }}
                    </span>
                    <span wire:loading.inline-flex wire:target="payOnline" class="inline-flex items-center gap-2">
                        <x-loading-spinner class="w-4 h-4" />
                        Processing...
                    </span>
                </button>
            </div>
        @endif

        <div class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200 p-6">
                    <h2 class="font-semibold text-stone-900 mb-4">Items</h2>
                    <div class="divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            @if ($item->basket)
                                @include('partials.order-basket-group', ['item' => $item, 'imageClass' => 'w-14 h-14'])
                            @else
                                <div class="flex items-center gap-4 py-3">
                                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if ($item->product)
                                            <img src="{{ $item->product->displayImageUrl() }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-2xl">🥗</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-stone-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-stone-400">{{ $item->quantity }} × {{ \Illuminate\Support\Number::currency($item->price, 'INR') }} @if ($item->unit) / {{ $item->unit }} @endif</p>
                                    </div>
                                    <p class="font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($item->total, 'INR') }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if ($order->notes)
                        <div class="mt-4 rounded-xl bg-stone-50 border border-stone-200 px-4 py-3 text-sm">
                            <span class="font-medium text-stone-700">Notes:</span> {{ $order->notes }}
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-stone-200 p-6">
                    <h2 class="font-semibold text-stone-900 mb-3">Delivery Address</h2>
                    <div class="inline-flex items-center gap-2 mb-2">
                        <span class="rounded-md bg-stone-100 border border-stone-200 px-2 py-0.5 text-xs text-stone-500">{{ $order->label ?? 'Home' }}</span>
                    </div>
                    <p class="text-sm font-medium text-stone-800">{{ $order->receiver_name }} · {{ $order->receiver_phone }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $order->address_line }}, {{ $order->city }}, {{ $order->state }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 p-6 lg:sticky lg:top-20">
                <h2 class="font-semibold text-stone-900 mb-4">Payment Summary</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal</span>
                        <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($order->subtotal, 'INR') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Delivery fee</span>
                        @if ($order->delivery_fee === 0)
                            <span class="font-medium text-green-600">FREE</span>
                        @else
                            <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($order->delivery_fee, 'INR') }}</span>
                        @endif
                    </div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span class="font-medium">−{{ \Illuminate\Support\Number::currency($order->discount, 'INR') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-stone-100 pt-3 flex justify-between">
                        <span class="font-semibold text-stone-900">Total</span>
                        <span class="font-bold text-lg text-stone-900">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Payment</span>
                        @if ($order->payment_method === 'cod')
                            <span class="font-medium text-stone-900">Cash on Delivery</span>
                        @elseif ($order->payment_status === 'paid')
                            <span class="font-medium text-green-600">Paid Online</span>
                        @else
                            <span class="font-medium text-amber-600">Payment Pending</span>
                        @endif
                    </div>
                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 text-sm font-semibold transition mt-2 w-full">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Download Invoice
                    </a>
                </div>
            </div>

            @if ($order->payment_method === 'online' && $order->payment_status === 'paid')
                <div class="bg-white rounded-2xl border border-stone-200 p-6">
                    <h2 class="font-semibold text-stone-900 mb-4">Payment Details</h2>
                    <dl class="space-y-2 text-sm">
                        @foreach (['method' => 'Method', 'status' => 'Status', 'amount' => 'Amount', 'vpa' => 'UPI', 'bank' => 'Bank', 'wallet' => 'Wallet', 'card' => 'Card', 'fee' => 'Fee', 'tax' => 'Tax'] as $key => $label)
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 capitalize">{{ $label }}</dt>
                                @if (($value = data_get($order->payment_details, $key)) !== null)
                                    <dd class="text-stone-800 text-right">{{ is_numeric($value) ? \Illuminate\Support\Number::currency($value / 100, 'INR') : $value }}</dd>
                                @else
                                    <dd class="text-stone-400 text-right">—</dd>
                                @endif
                            </div>
                        @endforeach
                        <div class="flex justify-between gap-4">
                            <dt class="text-stone-500">Payment ID</dt>
                            @if ($order->payment_id)
                                <dd class="text-stone-800 text-right break-all">{{ $order->payment_id }}</dd>
                            @else
                                <dd class="text-stone-400 text-right">—</dd>
                            @endif
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        @if ($order->status === \App\Models\Order::STATUS_PENDING)
            <div class="mt-8">
                @if (! $this->showCancelForm)
                    <button type="button" wire:click="$set('showCancelForm', true)" class="inline-flex items-center gap-2 rounded-xl border-2 border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 px-6 py-2.5 text-sm font-semibold transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Cancel Order
                    </button>
                @else
                    <div class="max-w-md bg-white rounded-2xl border-2 border-red-200 p-6">
                        <h3 class="font-semibold text-stone-900 mb-1">Cancel this order?</h3>
                        <p class="text-sm text-stone-500 mb-4">Please let us know why you're cancelling.</p>
                        <form wire:submit="cancelOrder" class="space-y-4">
                            <div>
                                <label for="cancelReason" class="block text-sm font-medium text-stone-700 mb-1">Reason <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="cancelReason" id="cancelReason" maxlength="200" placeholder="Tell us why you're cancelling…" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                                @error('cancelReason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 text-sm font-semibold transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    Confirm Cancellation
                                </button>
                                <button type="button" wire:click="$set('showCancelForm', false)" class="rounded-xl border border-stone-200 hover:bg-stone-50 px-6 py-2.5 text-sm font-semibold text-stone-600 transition">
                                    Keep Order
                                </button>
                            </div>
                        </form>
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
                        name: '{{ $order->receiver_name }}',
                        contact: '{{ $order->receiver_phone }}',
                    },
                    notes: {
                        order_id: '{{ $order->id }}',
                    },
                    theme: { color: payload.theme_color },
                    handler: (response) => {
                        fetch('{{ route('orders.payment.verify', $order) }}', {
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
                                    window.location.reload();
                                } else {
                                    alert(data.message || 'Payment could not be verified. Please try again.');
                                    window.location.reload();
                                }
                            })
                            .catch(() => {
                                alert('Something went wrong while verifying your payment. Please try again.');
                                window.location.reload();
                            });
                    },
                };

                try {
                    const razorpay = new window.Razorpay(options);
                    razorpay.open();
                } catch (error) {
                    console.error('Razorpay checkout failed', error);
                    alert('The payment window could not be opened. Make sure you are using HTTPS and try again.');
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
