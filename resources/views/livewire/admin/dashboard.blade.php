<div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Total Revenue</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="indian-rupee" class="w-4 h-4"></i></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($this->stats['revenue'], 'INR') }}</p>
            <p class="mt-1 text-xs text-stone-400">Excludes cancelled orders</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Total Orders</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="package" class="w-4 h-4"></i></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-stone-900">{{ $this->stats['orders'] }}</p>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" wire:navigate class="mt-1 inline-block text-xs text-amber-600 font-medium">{{ $this->pendingOrdersCount }} pending</a>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Customers</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="users" class="w-4 h-4"></i></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-stone-900">{{ $this->stats['customers'] }}</p>
            <a href="{{ route('admin.customers.index') }}" wire:navigate class="mt-1 inline-block text-xs text-brand-600 font-medium">View all →</a>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Products</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="shopping-basket" class="w-4 h-4"></i></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-stone-900">{{ $this->stats['products'] }}</p>
            <a href="{{ route('admin.products.index') }}" wire:navigate class="mt-1 inline-block text-xs text-brand-600 font-medium">Manage →</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-stone-200">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-900">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" wire:navigate class="text-xs font-semibold text-brand-600">View all →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($this->recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-stone-50 transition">
                        <div class="min-w-0">
                            <p class="font-medium text-sm text-stone-900 truncate">{{ $order->order_number }}</p>
                            <p class="text-xs text-stone-400 truncate">{{ $order->user?->name }} · {{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <x-status-badge :status="$order->status" />
                            <span class="font-semibold text-sm text-stone-900">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span>
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-stone-400">No orders yet.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-900">Low Stock Alerts</h2>
                <a href="{{ route('admin.products.index', ['low_stock' => 1]) }}" wire:navigate class="text-xs font-semibold text-brand-600">Manage →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($this->lowStockProducts as $product)
                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-stone-50 transition">
                        <div class="min-w-0">
                            <p class="font-medium text-sm text-stone-900 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-stone-400">{{ $product->category?->name }}</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">{{ $product->stock }} left</span>
                    </a>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-stone-400">All products are well stocked. 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
