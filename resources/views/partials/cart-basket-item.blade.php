<div class="flex gap-4 p-4" wire:key="cart-{{ $item->id }}">
    @if ($item->basket)
        <a href="{{ route('baskets.show', $item->basket) }}" wire:navigate class="w-20 h-20 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 flex items-center justify-center shrink-0 overflow-hidden">
            <img src="{{ str_replace('/storage/', '/public/storage/', $item->basket->displayImageUrl()) }}" alt="{{ $item->basket->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <a href="{{ route('baskets.show', $item->basket) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $item->basket->name }}</a>
                    <p class="text-xs text-stone-400 mt-0.5">{{ $item->basket->typeLabel() }} · {{ \Illuminate\Support\Number::currency($item->basket->price, 'INR') }}</p>
                </div>
                <button type="button" wire:click="remove({{ $item->id }})" wire:loading.attr="disabled" wire:target="remove({{ $item->id }})" class="text-stone-400 hover:text-red-600 disabled:opacity-40" aria-label="Remove">
                    <span wire:loading.remove wire:target="remove({{ $item->id }})"><i data-lucide="trash-2" class="w-5 h-5"></i></span>
                    <x-loading-spinner wire:loading wire:target="remove({{ $item->id }})" class="w-4 h-4" />
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center gap-3 bg-stone-100 rounded-lg p-1">
                    <button type="button" wire:click="decrement({{ $item->id }})" wire:loading.attr="disabled" wire:target="decrement({{ $item->id }})" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50 disabled:opacity-40" aria-label="Decrease quantity"><i data-lucide="minus" class="w-3.5 h-3.5"></i></button>
                    <span class="w-6 text-center font-medium text-sm">
                        <span wire:loading.remove wire:target="increment({{ $item->id }}),decrement({{ $item->id }})">{{ $item->quantity }}</span>
                        <x-loading-spinner wire:loading wire:target="increment({{ $item->id }}),decrement({{ $item->id }})" class="w-3.5 h-3.5 mx-auto" />
                    </span>
                    <button type="button" wire:click="increment({{ $item->id }})" wire:loading.attr="disabled" wire:target="increment({{ $item->id }})" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50 disabled:opacity-40" aria-label="Increase quantity"><i data-lucide="plus" class="w-3.5 h-3.5"></i></button>
                </div>
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($item->basket->price * $item->quantity, 'INR') }}</p>
                    <button type="button" @click="open = !open" class="p-1 text-stone-400 hover:text-stone-600 rounded-lg hover:bg-stone-50" aria-label="Toggle basket contents">
                        <span class="inline-flex transition-transform" :class="{ 'rotate-180': open }">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="w-20 h-20 rounded-xl bg-stone-100 flex items-center justify-center shrink-0 text-stone-400">
            <i data-lucide="package-x" class="w-6 h-6"></i>
        </div>
        <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
            <div>
                <p class="font-medium text-stone-900">{{ $item->name() }}</p>
                <p class="text-xs text-stone-400 mt-0.5">No longer available</p>
            </div>
            <button type="button" wire:click="remove({{ $item->id }})" wire:loading.attr="disabled" wire:target="remove({{ $item->id }})" class="text-stone-400 hover:text-red-600 disabled:opacity-40" aria-label="Remove">
                <span wire:loading.remove wire:target="remove({{ $item->id }})"><i data-lucide="trash-2" class="w-5 h-5"></i></span>
                <x-loading-spinner wire:loading wire:target="remove({{ $item->id }})" class="w-4 h-4" />
            </button>
        </div>
    @endif
</div>
