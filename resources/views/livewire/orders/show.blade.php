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
            <div>
                <x-status-badge :status="$order->status" />
            </div>
        </div>

        @if ($order->status === 'delivered')
            <div class="rounded-2xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 mb-6 inline-flex items-center gap-2"><i data-lucide="circle-check" class="w-5 h-5"></i> Order delivered successfully. Enjoy your fresh produce!</div>
        @elseif ($order->status === 'cancelled')
            <div class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 mb-6">This order was cancelled.</div>
        @endif

        <div class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200 p-6">
                    <h2 class="font-semibold text-stone-900 mb-4">Items</h2>
                    <div class="divide-y divide-stone-100">
                        @foreach ($order->items as $item)
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
                                <p class="font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($item->total, 'INR') }}</p>
                            </div>
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
                    <p class="mt-1 text-sm text-stone-500">{{ $order->address_line }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
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
                        <span class="font-medium text-stone-900">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid Online' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if (in_array($order->status, [\App\Models\Order::STATUS_PENDING, \App\Models\Order::STATUS_CONFIRMED], true))
            <div class="mt-8">
                <button type="button" wire:click="cancelOrder" wire:confirm="Are you sure you want to cancel this order?" class="inline-flex items-center gap-2 rounded-xl border-2 border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 px-6 py-2.5 text-sm font-semibold transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Cancel Order
                </button>
            </div>
        @endif
    </div>
</div>
