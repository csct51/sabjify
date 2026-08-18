<div
    class="group bg-white rounded-2xl border border-stone-200 hover:border-brand-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 overflow-hidden flex flex-col"
    wire:key="basket-{{ $basket->id }}"
    x-data="{
        items: @js($this->overlayItems()),
        name: @js($basket->name),
        count: @js($basket->products_count),
        url: @js(route('baskets.show', $basket)),
        open() {
            if (! window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                return;
            }

            const rect = this.$refs.card.getBoundingClientRect();

            window.dispatchEvent(new CustomEvent('basket-preview:open', {
                detail: {
                    name: this.name,
                    count: this.count,
                    url: this.url,
                    items: this.items,
                    rect: { left: rect.left, top: rect.top, right: rect.right, bottom: rect.bottom, width: rect.width, height: rect.height },
                },
            }));
        },
        close() {
            window.dispatchEvent(new CustomEvent('basket-preview:close'));
        },
    }"
    x-on:mouseenter="open()"
    x-on:mouseleave="close()"
    x-ref="card"
>
    <div class="p-4 pb-0">
        <a href="{{ route('baskets.show', $basket) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden rounded-xl">
            <img src="{{ $basket->displayImageUrl() }}" alt="{{ $basket->name }}" class="absolute inset-0 w-full h-full {{ $basket->imageFit() }} transition-transform duration-300 group-hover:scale-105">
        </a>
    </div>

    <div class="p-4 flex flex-col flex-1">
        <a href="{{ route('baskets.show', $basket) }}" wire:navigate class="font-semibold text-stone-900 hover:text-brand-700 line-clamp-1">{{ $basket->name }}</a>
        <p class="mt-1 text-xs text-stone-400">{{ $basket->products_count }} items inside</p>

        <div class="mt-auto pt-3 flex flex-wrap items-center justify-between gap-2">
            <p class="font-semibold text-stone-900 min-w-0">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</p>

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
        </div>
    </div>
</div>