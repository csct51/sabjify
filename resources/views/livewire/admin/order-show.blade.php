<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.orders.index') }}" wire:navigate class="hover:text-brand-600">← Orders</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-stone-900">{{ $order->order_number }}</h2>
            <p class="text-sm text-stone-400 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <x-status-badge :status="$order->status" />
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Items</h3>
                <div class="divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-4 py-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 flex items-center justify-center shrink-0 overflow-hidden">
                                @if ($item->product)
                                    <img src="{{ $item->product->displayImageUrl() }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl">🥗</span>
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

                <div class="border-t border-stone-100 mt-2 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal</span><span>{{ \Illuminate\Support\Number::currency($order->subtotal, 'INR') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Delivery fee</span>
                        <span>{{ $order->delivery_fee === 0 ? 'FREE' : \Illuminate\Support\Number::currency($order->delivery_fee, 'INR') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-stone-900">
                        <span>Total</span><span class="text-lg">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-3">Delivery Address</h3>
                <div class="inline-flex items-center gap-2 mb-2">
                    <span class="rounded-md bg-stone-100 border border-stone-200 px-2 py-0.5 text-xs text-stone-500">{{ $order->label ?? 'Home' }}</span>
                </div>
                <p class="text-sm font-medium text-stone-800">{{ $order->receiver_name }} · {{ $order->receiver_phone }}</p>
                <p class="mt-1 text-sm text-stone-500">{{ $order->address_line }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                @if ($order->notes)
                    <p class="mt-3 text-sm rounded-xl bg-stone-50 border border-stone-200 px-3 py-2 text-stone-600"><span class="font-medium">Notes:</span> {{ $order->notes }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Update Status</h3>
                <form wire:submit="updateStatus" class="space-y-3">
                    <select wire:model="status" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        @foreach (\App\Models\Order::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5 transition">Save Status</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Payment</h3>
                <div class="flex items-center justify-between text-sm mb-4">
                    <span class="text-stone-500">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online Payment' }}</span>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $order->payment_status === 'paid' ? 'border-green-200 bg-green-50 text-green-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                <form wire:submit="updatePaymentStatus" class="space-y-3">
                    <select wire:model="paymentStatus" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <button type="submit" class="w-full rounded-xl border border-stone-200 hover:bg-stone-50 text-sm font-semibold py-2.5 transition">Save Payment</button>
                </form>
            </div>

            @if (in_array($order->status, ['pending', 'confirmed'], true))
                <div class="bg-white rounded-2xl border border-red-200 p-6">
                    <h3 class="font-semibold text-red-700 mb-2">Cancel Order</h3>
                    <p class="text-xs text-stone-500 mb-4">Cancelling restocks all items in this order.</p>
                    <button type="button" wire:click="cancelOrder" wire:confirm="Cancel this order and restock items?" class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 transition">Cancel Order</button>
                </div>
            @endif
        </div>
    </div>
</div>
