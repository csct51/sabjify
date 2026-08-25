<div>
    <div class="bg-white border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-3xl mx-auto px-4 py-3">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                <input
                    type="search"
                    wire:model.live.debounce.200ms="search"
                    placeholder="Search fruits & vegetables..."
                    autofocus
                    class="w-full rounded-full border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
                />
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        @if ($this->items->isEmpty())
            <div class="text-center py-20">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-basket" class="w-8 h-8"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-stone-900">{{ $this->search ? 'No products found' : 'Start searching' }}</h3>
                <p class="text-sm text-stone-500 mt-1">{{ $this->search ? 'Try a different search term.' : 'Type a product name above to find fresh produce.' }}</p>
            </div>
        @else
            @if ($this->showSuggestions && $this->search && $this->suggestions->isNotEmpty())
                <ul data-suggestions class="mb-4 divide-y divide-stone-100 rounded-2xl border border-stone-200 bg-white overflow-hidden">
                    @foreach ($this->suggestions as $s)
                        <li>
                            <button type="button" wire:click="selectSuggestion({{ $s->id }})" class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left hover:bg-stone-50 transition">
                                <span class="text-sm font-medium text-stone-800">{{ $s->name }}</span>
                                @if ($s->category)
                                    <span class="text-xs text-stone-400 shrink-0">{{ $s->category->name }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($this->search)
                <p class="text-sm text-stone-500 mb-4">Showing results for "{{ $this->search }}"</p>
            @endif
            <div class="relative min-h-[240px]">
                <div wire:loading wire:target="search" class="absolute inset-0 z-50 bg-[#F7F8F5]/40 backdrop-blur-sm">
                    <div class="flex items-center justify-center w-full h-full">
                        <x-loading-spinner class="w-8 h-8 text-brand-600" />
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($this->items as $product)
                        <livewire:product-card :product="$product" :key="$product->id" />
                    @endforeach
                </div>

            @if ($this->hasMore)
                <div
                    x-intersect.full.margin.0px.0px.200px="$wire.loadMore()"
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
            </div>
        @endif
    </div>
</div>
