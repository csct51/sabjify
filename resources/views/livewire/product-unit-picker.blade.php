<div
    x-data="{
        open: false,
        focusables() {
            return Array.from($refs.dialog.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex=\'-1\'])'));
        },
        trapTab(e) {
            if (e.key !== 'Tab') return;
            const f = this.focusables();
            if (! f.length) return;
            const first = f[0];
            const last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (! e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        },
        focusFirst() {
            this.$nextTick(() => { if ($refs.dialog) $refs.dialog.focus(); });
        }
    }"
    x-on:product-unit-picker:open.window="open = true; $wire.open($event.detail.productId); focusFirst()"
    x-on:product-unit-picker:close.window="open = false; $wire.close()"
    x-cloak
>
    <div x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open = false; $wire.close()" aria-hidden="true"></div>
        <div x-ref="dialog" tabindex="-1" @keydown.tab="trapTab($event)" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xs flex flex-col max-h-[80vh] outline-none" x-transition @keydown.escape.window="open = false; $wire.close()" role="dialog" aria-modal="true" aria-labelledby="unit-picker-title">
            @if ($this->product)
                <div class="flex items-start justify-between gap-3 p-4 pb-3 border-b border-stone-100">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ str_replace('/storage/', '/public/storage/', $this->product->displayImageUrl()) }}" alt="{{ $this->product->name }}" class="w-10 h-10 rounded-lg border border-stone-200 object-cover shrink-0">
                        <div class="min-w-0">
                            <p id="unit-picker-title" class="text-sm font-semibold text-stone-900 truncate">{{ $this->product->name }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">Select a size</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false; $wire.close()" class="text-stone-400 hover:text-stone-600 shrink-0" aria-label="Close"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-3 gap-2">
                        @forelse ($this->product->units as $unit)
                            @php $optionAvailable = $unit->in_stock && $this->product->sellablePacksFor($unit) > 0; @endphp
                            <label
                                wire:key="unit-option-{{ $unit->id }}"
                                class="relative flex flex-col items-center justify-center gap-0.5 aspect-square rounded-lg border cursor-pointer transition {{ $this->selectedUnitId === $unit->id ? 'border-brand-600 bg-brand-50 ring-2 ring-brand-100' : 'border-stone-200 hover:border-brand-300' }} {{ $optionAvailable ? '' : 'opacity-60 cursor-not-allowed' }}"
                            >
                                <input type="radio" name="unit-option" value="{{ $unit->id }}" wire:model="selectedUnitId" wire:change="selectUnit({{ $unit->id }})" class="sr-only" {{ $this->selectedUnitId === $unit->id ? 'checked' : '' }} {{ $optionAvailable ? '' : 'disabled' }}>
                                <span class="text-xs font-semibold text-stone-900 leading-tight text-center px-1">{{ $unit->unit }}</span>
                                <span class="text-[11px] font-medium {{ $this->selectedUnitId === $unit->id ? 'text-brand-700' : 'text-stone-500' }}">{{ \Illuminate\Support\Number::currency($unit->price, 'INR') }}</span>
                                @unless ($optionAvailable)
                                    <span class="text-[10px] font-normal leading-tight text-red-500">Out of stock</span>
                                @endunless
                                <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 rounded-full border {{ $this->selectedUnitId === $unit->id ? 'border-brand-600' : 'border-stone-300' }}">
                                    @if ($this->selectedUnitId === $unit->id)
                                        <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="col-span-3 text-sm text-stone-500 py-6 text-center">No sizes available.</p>
                        @endforelse
                    </div>

                    @error('unit')
                        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('stock')
                        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 border-t border-stone-100 bg-white rounded-b-2xl">
                    @unless ($this->selectedUnitId)
                        <p class="text-xs text-red-500 mb-2">Please select a size to continue.</p>
                    @endunless

                    @php
                        $packsLeft = $this->product?->sellablePacksFor($this->selectedUnit()) ?? 0;
                        $showLeft = $this->selectedUnit()?->in_stock && $this->product && $packsLeft >= 1 && $packsLeft <= $this->product->lowPacksThreshold($this->selectedUnit());
                    @endphp
                    @if ($showLeft)
                        <p class="text-xs text-amber-600 mb-2">Only {{ $packsLeft }} left</p>
                    @endif

                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-1 bg-stone-100 rounded-lg p-0.5">
                            <button type="button" wire:click="decrementQuantity" wire:loading.attr="disabled" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50 disabled:opacity-40" aria-label="Decrease quantity"><i data-lucide="minus" class="w-3.5 h-3.5"></i></button>
                            <span class="w-6 text-center font-semibold text-sm">{{ $quantity }}</span>
                            <button type="button" wire:click="incrementQuantity" wire:loading.attr="disabled" class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center hover:bg-stone-50 disabled:opacity-40" aria-label="Increase quantity"><i data-lucide="plus" class="w-3.5 h-3.5"></i></button>
                        </div>
                        <span class="text-xs text-stone-500">
                            @if ($this->selectedUnit())
                                {{ \Illuminate\Support\Number::currency($this->selectedUnit()->price * $this->quantity, 'INR') }}
                            @endif
                        </span>
                    </div>
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" {{ $this->selectedUnitId ? '' : 'disabled' }} class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold py-2.5 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            Add to Cart
                        </span>
                        <span wire:loading.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                            <x-loading-spinner class="w-4 h-4" />
                            Adding...
                        </span>
                    </button>
                </div>
            @else
                <div class="p-10 flex items-center justify-center">
                    <x-loading-spinner class="w-6 h-6" />
                </div>
            @endif
        </div>
    </div>
</div>
