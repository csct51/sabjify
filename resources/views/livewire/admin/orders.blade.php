<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $orders->total() }} orders</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Orders</h2>
        </div>
    </div>

    <div class="flex gap-2 mb-6 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 no-scrollbar">
        @foreach (['all', 'pending', 'confirmed', 'packing', 'out_for_delivery', 'delivered', 'cancelled'] as $value)
            <button
                type="button"
                wire:click="filter('{{ $value }}')"
                wire:loading.attr="disabled"
                wire:target="filter('{{ $value }}')"
                class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition disabled:opacity-70 {{ ($this->status ?? 'all') === $value ? 'bg-stone-900 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-stone-300' }}"
            >
                {{ $value === 'all' ? 'All' : \App\Models\Order::STATUSES[$value] }}
                <span class="opacity-60">({{ $value === 'all' ? $this->counts['all'] : ($this->counts[$value] ?? 0) }})</span>
            </button>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="orders-table" class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium text-center">Items</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3 font-medium text-center">Payment</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-stone-50" wire:key="order-{{ $order->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="font-semibold text-brand-600 hover:text-brand-700">{{ $order->order_number }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-stone-900">{{ $order->user?->name }}</p>
                                <p class="text-xs text-stone-400">{{ $order->user?->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $order->created_at->format('d M, h:i A') }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $order->items_count }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $order->payment_status === 'paid' ? 'border-green-200 bg-green-50 text-green-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                    {{ $order->payment_method === 'cod' ? 'COD' : 'Online' }} · {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center"><x-status-badge :status="$order->status" /></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
