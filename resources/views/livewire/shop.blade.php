<div>
    <section class="bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <h1 class="text-xl font-bold text-stone-900">{{ $this->category ? collect($this->categories)->firstWhere('slug', $this->category)?->name : 'All Products' }}</h1>
            <p class="text-sm text-stone-500 mt-0.5">{{ $products->total() }} items available</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
        <aside data-reveal>
            <div class="bg-white rounded-2xl border border-stone-200 p-4 space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-stone-900 uppercase tracking-wide mb-3">Search</h2>
                    <input
                        wire:model="search"
                        type="search"
                        placeholder="Search products..."
                        class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-stone-900 uppercase tracking-wide mb-3">Sort By</h2>
                    <select wire:model="sort" class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        <option value="latest">Latest</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="popular">Popular</option>
                    </select>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-stone-900 uppercase tracking-wide mb-3">Categories</h2>
                    <select wire:model="category" class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        <option value="">All Products</option>
                        @foreach ($this->categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }} ({{ $cat->products_count }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 border-t border-stone-200 flex gap-2">
                    <button type="button" wire:click="resetPage" class="flex-1 rounded-xl bg-brand-600 text-white px-4 py-2 text-sm font-semibold hover:bg-brand-700 transition">Apply Filters</button>
                    <button type="button" wire:click="clearFilters" class="flex-1 rounded-xl border border-stone-300 text-stone-600 px-4 py-2 text-sm font-medium hover:bg-stone-50 transition">Clear Filters</button>
                </div>
            </div>
        </aside>

        <div data-reveal>
            @if ($products->isEmpty())
                <div class="text-center py-20">
                    <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-basket" class="w-8 h-8"></i></span>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900">No products found</h3>
                    <p class="text-sm text-stone-500 mt-1">Try a different search or category.</p>
                    <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-block rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($products as $product)
                        <livewire:product-card :product="$product" :key="$product->id" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
