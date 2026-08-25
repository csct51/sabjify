<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.customers.index') }}" wire:navigate class="hover:text-brand-600">← Customers</a>
    </nav>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <span class="flex items-center justify-center w-12 h-12 rounded-full bg-brand-100 text-brand-700 font-semibold text-lg">{{ $user->initials() }}</span>
            <div>
                <h2 class="text-lg font-semibold text-stone-900">{{ $user->name }}</h2>
                <p class="text-sm text-stone-400">Customer since {{ $user->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                {{ $user->is_active ? 'Active' : 'Blocked' }}
            </span>
            @if ($user->is_active)
                <button type="button" data-confirm-message="Block {{ $user->name }}? They won't be able to log in." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.toggleActive() })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Block</button>
            @else
                <button type="button" data-confirm-message="Unblock {{ $user->name }}? They'll be able to log in again." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.toggleActive() })" class="rounded-lg border border-green-200 hover:bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600">Unblock</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Total Orders</p>
            <p class="text-2xl font-bold text-stone-900">{{ $this->orders->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Total Spent</p>
            <p class="text-2xl font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($this->totalSpent, 'INR') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <p class="text-xs text-stone-400 uppercase tracking-wide mb-1">Active Orders</p>
            <p class="text-2xl font-bold text-stone-900">{{ $this->activeOrders }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="min-w-0 lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Order History</h3>
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
                @if ($this->orders->isEmpty())
                    <p class="text-sm text-stone-400">No orders yet.</p>
                @else
                    <div class="divide-y divide-stone-100">
                        @foreach ($this->orders as $order)
                            <div class="flex items-center justify-between gap-4 py-3" wire:key="customer-order-{{ $order->id }}">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-600">{{ $order->order_number }}</a>
                                    <p class="text-xs text-stone-400 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }} · {{ $order->items->count() }} items</p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-sm font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</span>
                                    <x-status-badge :status="$order->status" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="min-w-0 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Profile</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide">Name</dt>
                        <dd class="text-stone-800 mt-0.5">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide">Email</dt>
                        <dd class="text-stone-800 mt-0.5">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide">Phone</dt>
                        <dd class="text-stone-800 mt-0.5">+91 {{ $user->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 text-xs uppercase tracking-wide">Joined</dt>
                        <dd class="text-stone-800 mt-0.5">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900 mb-4">Saved Addresses</h3>
                @if ($user->addresses->isEmpty())
                    <p class="text-sm text-stone-400">No saved addresses.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($user->addresses as $address)
                            <div class="rounded-xl border border-stone-200 p-3" wire:key="customer-address-{{ $address->id }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-md bg-stone-100 border border-stone-200 px-2 py-0.5 text-xs text-stone-500">{{ $address->label }}</span>
                                    @if ($address->is_default)
                                        <span class="text-xs text-brand-600 font-medium">Default</span>
                                    @endif
                                </div>
                                <p class="text-sm text-stone-600 mt-2">{{ $address->receiver_name }} · {{ $address->receiver_phone }}</p>
                                <p class="text-xs text-stone-500 mt-1">{{ $address->address_line }}, {{ $address->landmark ? $address->landmark.', ' : '' }}{{ $address->city }}, {{ $address->state }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
