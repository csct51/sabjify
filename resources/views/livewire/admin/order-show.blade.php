<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.orders.index') }}" wire:navigate class="hover:text-brand-600">← Orders</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-stone-900">{{ $order->order_number }}</h2>
            <p class="text-sm text-stone-400 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                Download Invoice
            </a>
            <x-status-badge :status="$order->status" />
        </div>
    </div>

    @if ($order->status === 'cancelled' && $order->cancelled_by)
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <span class="font-medium">Cancelled by:</span> {{ $order->cancelled_by === 'customer' ? 'Customer' : 'Platform' }}
            @if ($order->cancelled_reason)
                <span class="font-medium">Reason:</span> {{ $order->cancelled_reason }}
            @endif
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="min-w-0 lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Items</h3>
                <div class="divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        @if ($item->basket)
                            @include('partials.order-basket-group', ['item' => $item])
                        @else
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
                                <p class="font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($item->total, 'INR') }}</p>
                            </div>
                        @endif
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
                <p class="mt-1 text-sm text-stone-500">{{ $order->address_line }}</p>
                @if ($order->notes)
                    <p class="mt-3 text-sm rounded-xl bg-stone-50 border border-stone-200 px-3 py-2 text-stone-600"><span class="font-medium">Notes:</span> {{ $order->notes }}</p>
                @endif
                @if ($order->latitude && $order->longitude)
                    <x-location-map
                        :lat="$order->latitude"
                        :lng="$order->longitude"
                        :routes="$coveringRoutes"
                        height="h-96"
                        readonly
                    />
                @endif
            </div>
        </div>

        <div class="min-w-0 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Update Status</h3>
                <form wire:submit="updateStatus" class="space-y-3">
                    <select wire:model="status" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        @foreach (\Illuminate\Support\Arr::except(\App\Models\Order::STATUSES, \App\Models\Order::STATUS_CANCELLED) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <button type="submit" wire:loading.attr="disabled" wire:target="updateStatus" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-2.5 transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="updateStatus" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="updateStatus">Save Status</span>
                        <span wire:loading wire:target="updateStatus">Saving...</span>
                    </button>
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
                @if ($order->payment_method === 'cod')
                    <form wire:submit="updatePaymentStatus" class="space-y-3">
                        <select wire:model="paymentStatus" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="refunded">Refunded</option>
                        </select>
                        <button type="submit" wire:loading.attr="disabled" wire:target="updatePaymentStatus" class="inline-flex items-center justify-center gap-2 w-full rounded-xl border border-stone-200 hover:bg-stone-50 text-sm font-semibold py-2.5 transition disabled:opacity-70">
                            <x-loading-spinner wire:loading wire:target="updatePaymentStatus" class="w-4 h-4" />
                            <span wire:loading.remove wire:target="updatePaymentStatus">Save Payment</span>
                            <span wire:loading wire:target="updatePaymentStatus">Saving...</span>
                        </button>
                    </form>
                @elseif ($order->payment_status === 'paid')
                    <dl class="space-y-2 text-sm">
                        @foreach (['method' => 'Method', 'status' => 'Status', 'amount' => 'Amount', 'vpa' => 'UPI', 'bank' => 'Bank', 'wallet' => 'Wallet', 'card' => 'Card', 'fee' => 'Fee', 'tax' => 'Tax'] as $key => $label)
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 capitalize">{{ $label }}</dt>
                                @if (($value = data_get($order->payment_details, $key)) !== null)
                                    <dd class="text-stone-800 text-right">{{ is_numeric($value) ? number_format($value / 100, 2) : $value }}</dd>
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
                @endif
            </div>

            @if (in_array($order->status, ['pending', 'confirmed'], true))
                <div class="bg-white rounded-2xl border border-red-200 p-6">
                    <h3 class="font-semibold text-red-700 mb-2">Cancel Order</h3>
                    <p class="text-xs text-stone-500 mb-4">The order will be cancelled and removed from fulfilment.</p>
                    <form wire:submit="cancelOrder" class="space-y-3">
                        <input type="text" wire:model="cancelReason" maxlength="200" placeholder="Enter reason for cancellation…" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        @error('cancelReason')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        <button type="submit" wire:loading.attr="disabled" wire:target="cancelOrder" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 transition disabled:opacity-70">
                            <x-loading-spinner wire:loading wire:target="cancelOrder" class="w-4 h-4" />
                            <span wire:loading.remove wire:target="cancelOrder">Cancel Order</span>
                            <span wire:loading wire:target="cancelOrder">Cancelling...</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
