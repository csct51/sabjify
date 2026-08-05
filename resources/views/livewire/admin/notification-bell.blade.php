<div>
    <button
        type="button"
        wire:click="toggle"
        class="relative flex items-center justify-center w-9 h-9 rounded-lg hover:bg-stone-100 text-stone-400 hover:text-stone-600"
        aria-label="Notifications"
        title="Pending orders"
    >
        <i data-lucide="bell" class="w-5 h-5"></i>
        @if ($this->pendingOrdersCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-xs font-semibold flex items-center justify-center">
                {{ $this->pendingOrdersCount }}
            </span>
        @endif
    </button>

    @if ($this->show)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="notification-modal">
            <div class="absolute inset-0 bg-stone-900/50" wire:click="close"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                    <div>
                        <h3 class="font-semibold text-stone-900">Pending Orders</h3>
                        <p class="text-xs text-stone-400">{{ $this->pendingOrdersCount }} orders awaiting action</p>
                    </div>
                    <button type="button" wire:click="close" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-stone-100 text-stone-400 hover:text-stone-600" aria-label="Close">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto divide-y divide-stone-100">
                    @forelse ($this->pendingOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" wire:navigate wire:click="close" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-stone-50 transition">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-stone-900 truncate">{{ $order->order_number }}</p>
                                <p class="text-xs text-stone-400 truncate">{{ $order->user?->name }} · {{ \Illuminate\Support\Number::currency($order->total, 'INR') }}</p>
                            </div>
                            <span class="shrink-0 text-xs text-stone-400">{{ $order->created_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-stone-400">No pending orders. All caught up!</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-5 py-3 border-t border-stone-100">
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" wire:navigate wire:click="close" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        View all pending orders <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
