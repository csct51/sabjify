<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $products->total() }} products</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Products</h2>
        </div>
        <a href="{{ route('admin.products.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Product
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap gap-3">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search products..." class="w-full sm:w-72 rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            <select wire:model.live="category" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 rounded-xl border border-stone-300 px-3 py-2 text-sm cursor-pointer">
                <input type="checkbox" wire:model.live="lowStock" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                Low stock only
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium text-right">Price</th>
                        <th class="px-4 py-3 font-medium text-center">Stock</th>
                        <th class="px-4 py-3 font-medium text-center">Featured</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-stone-50" wire:key="prod-{{ $product->id }}">
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
                                <p class="font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($product->price, 'INR') }}</p>
                                @if ($product->mrp && $product->mrp > $product->price)
                                    <p class="text-xs text-stone-400 line-through">{{ \Illuminate\Support\Number::currency($product->mrp, 'INR') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $product->stock <= 10 ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleFeatured({{ $product->id }})" class="transition {{ $product->is_featured ? 'text-amber-500 fill-amber-500' : 'text-stone-300' }} hover:scale-110" aria-label="Toggle featured"><i data-lucide="star" class="w-4 h-4"></i></button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $product->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $product->is_active ? 'text-green-600' : 'text-stone-400' }}">
                                    <span class="w-2 h-2 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    {{ $product->is_active ? 'Active' : 'Hidden' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="text-brand-600 hover:text-brand-700 font-medium text-xs">Edit</a>
                                    <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Delete {{ $product->name }}?" class="text-red-600 hover:text-red-700 font-medium text-xs">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center text-stone-400">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-100">
            {{ $products->links() }}
        </div>
    </div>
</div>
