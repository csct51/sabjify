<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-semibold text-stone-900">Stock Quantity</h1>
            <p class="text-sm text-stone-500 mt-0.5">Current stock per product (from purchases, admin-only).</p>
        </div>
    </div>

    <div class="grid sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-stone-200 p-4">
            <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Total Products</p>
            <p class="mt-1 text-2xl font-bold text-stone-900">{{ $this->summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-green-200 p-4">
            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">In Stock</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ $this->summary['in'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200 p-4">
            <p class="text-xs font-medium text-amber-600 uppercase tracking-wide">Low Stock</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ $this->summary['low'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-red-200 p-4">
            <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Out of Stock</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $this->summary['out'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap items-center gap-3">
            <select wire:model.live="category" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="stockFilter" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="all">All Stock</option>
                <option value="in">In Stock</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>
            <div class="relative flex-1 max-w-sm ml-auto">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product..." class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Product</th>
                        <th class="px-4 py-3 text-left font-medium">Category</th>
                        <th class="px-4 py-3 text-right font-medium">Current Stock</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-center font-medium">Storefront</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-stone-50" wire:key="stock-{{ $product->id }}">
                            <td class="px-4 py-3 text-stone-400">{{ $products->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </span>
                                    <span class="font-medium text-stone-900">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $product->category?->name ?? '—' }}</td>
                            @php
                                $stockFloat = (float) $product->current_stock;
                                $stockDisplay = rtrim(rtrim(number_format($stockFloat, 3, '.', ''), '0'), '.');
                                $isKg = $product->purchaseUnit() === 'kg';
                                $isLow = $product->isLowStock();
                            @endphp
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $stockFloat <= 0 ? 'bg-red-50 text-red-700 border border-red-200' : ($isLow ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-green-50 text-green-700 border border-green-200') }}">
                                    {{ $stockDisplay }}{{ $isKg ? ' g' : '' }}
                                </span>
                                @if ($isKg && $stockFloat > 0)
                                    <p class="mt-0.5 text-[11px] text-stone-400">≈ {{ rtrim(rtrim(number_format($stockFloat / 1000, 3, '.', ''), '0'), '.') }} kg</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($stockFloat <= 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-50 text-red-700 border border-red-200">Out of stock</span>
                                @elseif ($isLow)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">Low ({{ $stockDisplay }})</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200">In stock</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    type="button"
                                    wire:click="toggleStock({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleStock({{ $product->id }})"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition disabled:opacity-50 {{ $product->inStock() ? 'bg-brand-600' : 'bg-stone-300' }}"
                                    role="switch"
                                    aria-checked="{{ $product->inStock() ? 'true' : 'false' }}"
                                    aria-label="Toggle storefront visibility for {{ $product->name }}"
                                >
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition {{ $product->inStock() ? 'translate-x-[22px]' : 'translate-x-0.5' }}"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="clipboard-list" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No products found</p>
                                <p class="text-xs text-stone-500 mt-1">Try a different search or filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
