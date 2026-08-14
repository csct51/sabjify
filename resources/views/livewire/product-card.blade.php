<div class="group bg-white rounded-2xl border border-stone-200 hover:border-brand-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 overflow-hidden flex flex-col" wire:key="product-{{ $product->id }}">
    <div class="p-3 pb-0">
        <a href="{{ route('product.show', $product->slug) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden rounded-xl">
            <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full {{ $product->imageFit() }} transition-transform duration-300 group-hover:scale-105">

            @if ($product->discountPercent() > 0)
                <span class="absolute top-1.5 left-1.5 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ $product->discountPercent() }}% OFF</span>
            @endif

            @if (! $product->inStock())
                <span class="absolute inset-0 bg-white/70 flex items-center justify-center">
                    <span class="bg-stone-900 text-white text-[10px] font-semibold px-2 py-1 rounded-md">Out of Stock</span>
                </span>
            @endif
        </a>
    </div>

    <div class="p-3 flex flex-col flex-1">
        <p class="text-[10px] uppercase tracking-wide text-stone-400 font-medium">{{ $product->category?->name }}</p>
        <a href="{{ route('product.show', $product->slug) }}" wire:navigate class="mt-0.5 text-sm font-medium text-stone-900 leading-snug hover:text-brand-700">{{ $product->name }}</a>
        @if ($product->hasMultipleUnits())
            <p class="text-[11px] text-stone-400 mt-0.5">Multiple sizes available</p>
        @else
            <p class="text-[11px] text-stone-400 mt-0.5">per {{ $product->defaultUnit()?->unit ?? $product->unit }}</p>
        @endif

        <div class="mt-auto pt-2 flex flex-wrap items-end justify-between gap-2">
            <div class="min-w-0">
                @php $unit = $product->defaultUnit(); @endphp
                @if ($product->hasMultipleUnits())
                    <p class="text-xs text-stone-400">From</p>
                    @if ($product->discountPercent() > 0 && $unit?->mrp)
                        <p class="text-xs text-stone-400 line-through">{{ \Illuminate\Support\Number::currency($unit->mrp, 'INR') }}</p>
                    @endif
                    <p class="text-base font-bold text-stone-900 -mt-1">{{ \Illuminate\Support\Number::currency($product->minPrice(), 'INR') }}</p>
                @else
                    @if ($product->discountPercent() > 0 && $unit?->mrp)
                        <p class="text-xs text-stone-400 line-through">{{ \Illuminate\Support\Number::currency($unit->mrp, 'INR') }}</p>
                    @endif
                    <p class="text-base font-bold text-stone-900 -mt-1">{{ \Illuminate\Support\Number::currency($product->minPrice(), 'INR') }}</p>
                @endif
            </div>

            @if ($product->inStock())
                @if ($product->hasMultipleUnits())
                    <button type="button" @click="$dispatch('product-unit-picker:open', { productId: {{ $product->id }} })" class="shrink-0 inline-flex items-center gap-0.5 px-2.5 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 active:scale-95 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add
                    </button>
                @elseif ($this->inCart)
                    <div class="flex items-center gap-0.5 bg-brand-600 text-white rounded-lg p-0.5 shrink-0">
                        <button type="button" wire:click="decrement" wire:loading.attr="disabled" wire:target="decrement" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-brand-700" aria-label="Decrease quantity"><i data-lucide="minus" class="w-3 h-3"></i></button>
                        <span class="w-5 text-center text-xs font-semibold">
                            <span wire:loading.remove wire:target="increment,decrement">{{ $quantity }}</span>
                            <x-loading-spinner wire:loading wire:target="increment,decrement" class="w-3 h-3 mx-auto" />
                        </span>
                        <button type="button" wire:click="increment" wire:loading.attr="disabled" wire:target="increment" class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-brand-700" aria-label="Increase quantity"><i data-lucide="plus" class="w-3 h-3"></i></button>
                    </div>
                @else
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="shrink-0 inline-flex items-center gap-0.5 px-2.5 py-1.5 bg-brand-600 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 active:scale-95 transition">
                        <span wire:loading.remove.inline-flex wire:target="addToCart" class="inline-flex items-center gap-0.5">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add
                        </span>
                        <x-loading-spinner wire:loading wire:target="addToCart" class="w-3.5 h-3.5 mx-auto" />
                    </button>
                @endif
            @endif
        </div>

        @error('stock')
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
