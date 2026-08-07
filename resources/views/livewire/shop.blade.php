<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 mb-1">{{ $this->category ? collect($this->categories)->firstWhere('slug', $this->category)?->name : 'All Products' }}</h1>
                <p class="text-sm text-stone-500">{{ $products->total() }} items available</p>
            </div>
            <button type="button" wire:click="$set('showFilters', true)" class="lg:hidden inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50 transition">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filters
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8">
        <aside data-reveal class="hidden lg:block">
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

    @if ($this->showFilters)
        <div class="fixed inset-0 z-50 flex items-end lg:hidden" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-stone-900/50" wire:click="$set('showFilters', false)"></div>
            <div class="relative w-full bg-white rounded-t-2xl max-h-[85vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-stone-100 px-5 py-4 flex items-center justify-between">
                    <h2 class="font-semibold text-stone-900">Filters</h2>
                    <button type="button" wire:click="$set('showFilters', false)" class="text-stone-400 hover:text-stone-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-5 space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-900 uppercase tracking-wide mb-3">Sort By</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['latest' => 'Latest', 'price_low' => 'Price: Low to High', 'price_high' => 'Price: High to Low', 'popular' => 'Popular'] as $value => $label)
                                <button
                                    type="button"
                                    wire:click="$set('sort', '{{ $value }}')"
                                    class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $this->sort === $value ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-stone-900 uppercase tracking-wide mb-3">Categories</h3>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="$set('category', null)"
                                class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $this->category === null ? 'bg-brand-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}"
                            >
                                All
                            </button>
                            @foreach ($this->categories as $cat)
                                <button
                                    type="button"
                                    wire:click="$set('category', '{{ $cat->slug }}')"
                                    class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $this->category === $cat->slug ? 'bg-brand-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}"
                                >
                                    {{ $cat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 flex gap-2">
                        <button type="button" wire:click="clearFilters" class="flex-1 rounded-xl border border-stone-300 text-stone-600 px-4 py-2.5 text-sm font-medium hover:bg-stone-50 transition">Clear Filters</button>
                        <button type="button" wire:click="$set('showFilters', false)" class="flex-1 rounded-xl bg-brand-600 text-white px-4 py-2.5 text-sm font-semibold hover:bg-brand-700 transition">Apply Filters</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
