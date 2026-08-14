<div class="flex items-center gap-4 p-4" wire:key="recipe-product-{{ $product->id }}">
    @php($pivotUnit = $product->units->firstWhere('id', $unitId))
    @php($displayUnit = $pivotUnit?->unit ?? $product->units->first()?->unit ?? $product->unit)
    @php($displayPrice = $pivotUnit?->price ?? $product->units->first()?->price ?? $product->price)

    <span class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
    </span>

    <div class="flex-1 min-w-0">
        <a href="{{ route('product.show', $product->slug) }}" wire:navigate class="text-sm font-medium text-stone-800 hover:text-brand-700 truncate block">{{ $product->name }}</a>
        <p class="text-xs text-stone-400">{{ $displayUnit }}</p>
    </div>

    <p class="text-sm font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($displayPrice, 'INR') }}</p>

    @if ($product->inStock())
        @if ($inCart)
            <div class="flex items-center gap-1 bg-brand-600 text-white rounded-lg p-1 shrink-0">
                <button type="button" wire:click="decrement" wire:loading.attr="disabled" wire:target="decrement" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-brand-700" aria-label="Decrease quantity"><i data-lucide="minus" class="w-3 h-3"></i></button>
                <span class="w-6 text-center text-xs font-semibold">
                    <span wire:loading.remove wire:target="increment,decrement">{{ $quantity }}</span>
                    <x-loading-spinner wire:loading wire:target="increment,decrement" class="w-3 h-3 mx-auto" />
                </span>
                <button type="button" wire:click="increment" wire:loading.attr="disabled" wire:target="increment" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-brand-700" aria-label="Increase quantity"><i data-lucide="plus" class="w-3 h-3"></i></button>
            </div>
        @else
            <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="shrink-0 inline-flex items-center gap-0.5 px-3 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 active:scale-95 transition">
                <span wire:loading.remove.inline-flex wire:target="addToCart" class="inline-flex items-center gap-0.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add
                </span>
                <x-loading-spinner wire:loading wire:target="addToCart" class="w-3.5 h-3.5 mx-auto" />
            </button>
        @endif
    @endif
</div>
