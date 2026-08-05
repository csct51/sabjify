<div>
    <div class="bg-white border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-3xl mx-auto px-4 py-3">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                <input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search fruits & vegetables..."
                    autofocus
                    class="w-full rounded-full border-stone-200 bg-stone-50 py-2.5 pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
                />
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        @if ($products->isEmpty())
            <div class="text-center py-20">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-basket" class="w-8 h-8"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-stone-900">{{ $this->search ? 'No products found' : 'Start searching' }}</h3>
                <p class="text-sm text-stone-500 mt-1">{{ $this->search ? 'Try a different search term.' : 'Type a product name above to find fresh produce.' }}</p>
            </div>
        @else
            <p class="text-sm text-stone-500 mb-4">{{ $products->total() }} {{ $products->total() === 1 ? 'item' : 'items' }} found</p>
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
