<div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-stone-900">My Orders</h1>
            <a href="{{ route('shop') }}" wire:navigate class="text-sm font-semibold text-brand-600 hover:text-brand-700">+ Order more</a>
        </div>

        <div class="flex gap-2 flex-wrap mb-6">
            @foreach ([null, 'pending', 'confirmed', 'out_for_delivery', 'delivered', 'cancelled'] as $value)
                <button
                    type="button"
                    wire:click="$set('status', '{{ $value }}')"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $this->status === $value ? 'bg-stone-900 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-stone-300' }}"
                >
                    {{ $value ? \App\Models\Order::STATUSES[$value] : 'All' }}
                </button>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <div class="bg-white rounded-2xl border border-stone-200 text-center py-20">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="package" class="w-8 h-8"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">No orders here</h2>
                <p class="text-sm text-stone-500 mt-1">When you place an order, it will show up here.</p>
                <a href="{{ route('shop') }}" wire:navigate class="mt-5 inline-block rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition">Browse Products</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white rounded-2xl border border-stone-200 hover:border-brand-300 transition p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $order->order_number }}</p>
                                <p class="text-xs text-stone-400 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$order->status" />
                                <span class="font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <p class="text-stone-500">{{ $order->items->sum('quantity') }} item(s) · {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid Online' }}</p>
                            <span class="text-brand-600 text-sm font-medium">View details →</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
