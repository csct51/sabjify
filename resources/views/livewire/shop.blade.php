<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 mb-1">{{ $this->category ? collect($this->categories)->firstWhere('slug', $this->category)?->name : 'All Products' }}</h1>
                <p class="text-sm text-stone-500">{{ $this->resultCount() }} items available</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden lg:flex items-center gap-2">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Search products..."
                        class="w-56 rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                    <label class="text-sm text-stone-500">Sort by</label>
                    <select wire:model.live="sort" class="rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="latest">Latest</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="popular">Popular</option>
                    </select>
                    <button type="button" wire:click="clearFilters" class="rounded-xl border border-stone-300 text-stone-600 px-4 py-2 text-sm font-medium hover:bg-stone-50 transition">Clear Filters</button>
                </div>
                <button type="button" wire:click="$set('showFilters', true)" class="lg:hidden inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50 transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filters
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 grid grid-cols-1 lg:grid-cols-[170px_1fr] gap-8">
        <aside data-reveal class="block sticky top-20 z-30 -mx-4 px-4 pt-2 pb-2 bg-[#F7F8F5]/70 backdrop-blur-md lg:mx-0 lg:px-0 lg:pt-0 lg:pb-0 lg:bg-transparent lg:static">
            <div class="lg:sticky lg:top-24">
            <div>
                <h2 class="text-xs font-semibold text-stone-900 uppercase tracking-wide mb-3">Categories</h2>
                <div
                    x-data="{
                        canLeft: false,
                        canRight: false,
                        canScroll: false,
                        thumbSize: 0,
                        thumbOffset: 0,
                        update() {
                            const el = this.$refs.track;
                            const max = el.scrollWidth - el.clientWidth;
                            this.canScroll = max > 4;
                            this.canLeft = el.scrollLeft > 4;
                            this.canRight = el.scrollLeft < max - 4;
                            this.thumbSize = max > 0 ? (el.clientWidth / el.scrollWidth) * 100 : 100;
                            this.thumbOffset = max > 0 ? (el.scrollLeft / max) * (100 - this.thumbSize) : 0;
                        },
                        scrollBy(dir) {
                            this.$refs.track.scrollBy({ left: dir * 220, behavior: 'smooth' });
                        },
                        scrollToTrack(event) {
                            const el = this.$refs.track;
                            const rect = this.$refs.sbar.getBoundingClientRect();
                            const ratio = Math.min(Math.max((event.clientX - rect.left) / rect.width, 0), 1);
                            const max = el.scrollWidth - el.clientWidth;
                            el.scrollLeft = ratio * max;
                        },
                        startDrag(event) {
                            const el = this.$refs.track;
                            const rect = this.$refs.sbar.getBoundingClientRect();
                            const max = el.scrollWidth - el.clientWidth;
                            const startX = event.clientX;
                            const startScroll = el.scrollLeft;
                            const move = (e) => {
                                const delta = ((e.clientX - startX) / rect.width) * max;
                                el.scrollLeft = Math.min(Math.max(startScroll + delta, 0), max);
                            };
                            const up = () => {
                                window.removeEventListener('pointermove', move);
                                window.removeEventListener('pointerup', up);
                            };
                            window.addEventListener('pointermove', move);
                            window.addEventListener('pointerup', up);
                        }
                    }"
                    x-init="$nextTick(() => update()); window.addEventListener('resize', () => update());"
                    class="relative"
                >
                    <button
                        type="button"
                        @click="scrollBy(-1)"
                        x-show="canLeft"
                        x-transition.opacity
                        aria-label="Scroll categories left"
                        class="lg:hidden absolute left-0 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-7 h-9 rounded-r-lg bg-white/85 shadow-sm text-stone-600 hover:text-brand-600 hover:bg-white"
                    >
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>

                    <div x-ref="track" @scroll="update()" class="flex lg:flex-col gap-1.5 lg:max-h-80 lg:overflow-y-auto lg:pr-1 overflow-x-auto shop-cats-scroll">
                        <button
                            type="button"
                            wire:click="$set('category', null)"
                            class="shrink-0 lg:w-full flex flex-col items-center gap-1 rounded-lg px-2 py-2 text-center transition {{ $this->category === null ? 'bg-brand-600 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}"
                        >
                            <span class="w-12 h-12 lg:w-16 lg:h-16 rounded flex items-center justify-center {{ $this->category === null ? 'bg-white/20 text-white' : 'bg-white text-brand-600 shadow-sm' }}">
                                <i data-lucide="layout-grid" class="w-4 h-4 lg:w-5 lg:h-5"></i>
                            </span>
                            <span class="text-xs font-medium leading-tight line-clamp-1">All</span>
                        </button>

                        @foreach ($this->categories as $cat)
                            <button
                                type="button"
                                wire:click="$set('category', '{{ $cat->slug }}')"
                                class="shrink-0 lg:w-full flex flex-col items-center gap-1 rounded-lg px-2 py-2 text-center transition {{ $this->category === $cat->slug ? 'bg-brand-600 text-white shadow-sm' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}"
                            >
                                <span class="w-12 h-12 lg:w-16 lg:h-16 rounded bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                    @if ($cat->image)
                                        <img src="{{ str_replace('/storage/', '/public/storage/', $cat->imageUrl()) }}" alt="{{ $cat->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                    @endif
                                </span>
                                <span class="text-xs font-medium leading-tight line-clamp-1">{{ $cat->name }}</span>
                            </button>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        @click="scrollBy(1)"
                        x-show="canRight"
                        x-transition.opacity
                        aria-label="Scroll categories right"
                        class="lg:hidden absolute right-0 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-7 h-9 rounded-l-lg bg-white/85 shadow-sm text-stone-600 hover:text-brand-600 hover:bg-white"
                    >
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>

                    <div x-show="canLeft" class="lg:hidden pointer-events-none absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-[#F7F8F5] to-transparent"></div>
                    <div x-show="canRight" class="lg:hidden pointer-events-none absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-[#F7F8F5] to-transparent"></div>

                    <div
                        x-show="canScroll"
                        x-cloak
                        class="lg:hidden mt-3 relative shop-cats-scrollbar"
                        x-ref="sbar"
                        @click="scrollToTrack($event)"
                    >
                        <div
                            class="shop-cats-scrollbar__thumb"
                            x-ref="sthumb"
                            x-cloak
                            @click.stop
                            @pointerdown.prevent="startDrag($event)"
                            x-bind:style="`left:${thumbOffset}%; width:${thumbSize}%`"
                        ></div>
                    </div>
                </div>
                </div>
            </div>
        </aside>

        <div data-reveal class="relative min-h-[240px]">
            @if ($this->items->isEmpty())
                <div class="text-center py-20">
                    <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-basket" class="w-8 h-8"></i></span>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900">No products found</h3>
                    <p class="text-sm text-stone-500 mt-1">Try a different search or category.</p>
                    <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-block rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach ($this->items as $product)
                        <livewire:product-card :product="$product" :key="$product->id" />
                    @endforeach
                </div>

                @if ($this->hasMore)
                    <div
                        x-data="{ sent: false }"
                        x-intersect.full.margin.0px.0px.200px="if (!sent && $wire.hasMore && !$wire.loadingMore) { sent = true; $wire.loadMore(); }"
                        class="mt-8 flex justify-center"
                    >
                        <x-loading-spinner wire:loading wire:target="loadMore" class="w-6 h-6 text-brand-600" />
                    </div>

                    <div class="mt-4 flex justify-center" wire:loading.remove wire:target="loadMore">
                        <button type="button" wire:click="loadMore" class="rounded-xl border border-stone-300 text-stone-600 px-5 py-2.5 text-sm font-medium hover:bg-stone-50 transition">
                            Load more
                        </button>
                    </div>
                @endif
            @endif
        </div>

        <div wire:loading wire:target="search,sort,category" class="fixed inset-0 z-50 bg-[#F7F8F5]/40 backdrop-blur-sm">
            <div class="flex items-center justify-center w-full h-full">
                <x-loading-spinner class="w-8 h-8 text-brand-600" />
            </div>
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

                    <div class="pt-2 flex gap-2">
                        <button type="button" wire:click="clearFilters" class="flex-1 rounded-xl border border-stone-300 text-stone-600 px-4 py-2.5 text-sm font-medium hover:bg-stone-50 transition">Clear Filters</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
