<div class="flex gap-4 p-4" wire:key="cart-{{ $item->id }}">
    <a href="{{ route('product.show', $item->product->slug) }}" wire:navigate class="w-20 h-20 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 flex items-center justify-center shrink-0 overflow-hidden">
        <img src="{{ $item->product->displayImageUrl() }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div>
                <a href="{{ route('product.show', $item->product->slug) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $item->product->name }}</a>
                <p class="text-xs text-stone-400 mt-0.5">{{ $item->product->unit }} · {{ \Illuminate\Support\Number::currency($item->product->price, 'INR') }}</p>
            </div>
            <button type="button" wire:click="remove({{ $item->id }})" class="text-stone-400 hover:text-red-600" aria-label="Remove">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <div class="flex items-center gap-3 bg-stone-100 rounded-lg p-1">
                <button type="button" wire:click="decrement({{ $item->id }})" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50" aria-label="Decrease quantity"><i data-lucide="minus" class="w-3.5 h-3.5"></i></button>
                <span class="w-6 text-center font-medium text-sm">{{ $item->quantity }}</span>
                <button type="button" wire:click="increment({{ $item->id }})" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50" aria-label="Increase quantity"><i data-lucide="plus" class="w-3.5 h-3.5"></i></button>
            </div>
            <p class="font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($item->product->price * $item->quantity, 'INR') }}</p>
        </div>
        @if (! $item->product->inStock())
            <p class="mt-1 text-xs text-red-500">Only {{ $item->product->stock }} left in stock</p>
        @endif
    </div>
</div>
