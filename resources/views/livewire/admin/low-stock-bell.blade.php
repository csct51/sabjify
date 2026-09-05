<div>
    <button
        type="button"
        wire:click="toggle"
        class="relative flex items-center justify-center w-9 h-9 rounded-lg hover:bg-amber-50 text-stone-400 hover:text-amber-600"
        aria-label="Low stock alerts"
        title="Low stock"
    >
        <i data-lucide="triangle-alert" class="w-5 h-5"></i>
        @if ($this->lowStockCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 rounded-full bg-amber-500 text-white text-xs font-semibold flex items-center justify-center">
                {{ $this->lowStockCount }}
            </span>
        @endif
    </button>

    @if ($this->show)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:key="low-stock-modal">
            <div class="absolute inset-0 bg-stone-900/50" wire:click="close"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                    <div>
                        <h3 class="font-semibold text-stone-900">Low Stock</h3>
                        <p class="text-xs text-stone-400">{{ $this->lowStockCount }} products at or below alert level</p>
                    </div>
                    <button type="button" wire:click="close" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-stone-100 text-stone-400 hover:text-stone-600" aria-label="Close">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto divide-y divide-stone-100">
                    @forelse ($this->lowStockProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}" wire:navigate wire:click="close" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-stone-50 transition">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-stone-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-stone-400 truncate">{{ $product->category?->name ?? '—' }} · {{ $product->displayStock() }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">Low</span>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-stone-400">No low-stock products. All stocked up!</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-5 py-3 border-t border-stone-100">
                    <a href="{{ route('admin.reports.stock', ['stockFilter' => 'low']) }}" wire:navigate wire:click="close" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        View low stock report <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
