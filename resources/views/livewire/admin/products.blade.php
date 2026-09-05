<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $products->count() }} products</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Products</h2>
        </div>
        <a href="{{ route('admin.products.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Product
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap gap-3">
            <select wire:model.live="category" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table id="products-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium text-right">Price</th>
                        <th class="px-4 py-3 font-medium text-center">Stock</th>
                        <th class="px-4 py-3 font-medium text-center">Qty</th>
                        <th class="px-4 py-3 font-medium text-center">Featured</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($products as $product)
                        <tr class="hover:bg-stone-50" wire:key="prod-{{ $product->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $product->name }}</a>
                                        <p class="text-xs text-stone-400">{{ $product->unit }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $product->category?->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <p class="font-semibold text-stone-900">
                                    @if ($product->hasMultipleUnits())
                                        From
                                    @endif
                                    {{ \Illuminate\Support\Number::currency($product->minPrice(), 'INR') }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $product->inStock() ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    {{ $product->inStock() ? 'In stock' : 'Out of stock' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ (float) $product->current_stock > 0 ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-stone-100 text-stone-500 border border-stone-200' }}">
                                    {{ $product->displayStock() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleFeatured({{ $product->id }})" class="transition {{ $product->is_featured ? 'text-amber-500 fill-amber-500' : 'text-stone-300' }} hover:scale-110" aria-label="Toggle featured"><i data-lucide="star" class="w-4 h-4"></i></button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $product->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $product->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $product->is_active ? 'text-green-600' : 'text-stone-400' }} disabled:opacity-50">
                                    <x-loading-spinner wire:loading wire:target="toggleActive({{ $product->id }})" class="w-3 h-3" />
                                    <span wire:loading.remove wire:target="toggleActive({{ $product->id }})" class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    <span wire:loading.remove wire:target="toggleActive({{ $product->id }})">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">Edit</a>
                                    <button type="button" data-confirm-message="Delete {{ $product->name }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $product->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
