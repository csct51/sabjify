<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-stone-400 mb-6 flex items-center gap-1.5 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600">Home</a>
            <span>/</span>
            <a href="{{ route('baskets.index') }}" wire:navigate class="hover:text-brand-600">Baskets</a>
            <span>/</span>
            <span class="text-stone-600 font-medium">{{ $basket->name }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10" data-reveal>
            <div class="relative bg-gradient-to-br from-brand-50 to-lime-100 rounded-3xl border border-stone-200 aspect-[4/3] overflow-hidden">
                <img src="{{ $basket->displayImageUrl() }}" alt="{{ $basket->name }}" class="absolute inset-0 w-full h-full object-cover">
            </div>

            <div>
                <span class="inline-flex items-center rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-2.5 py-1">{{ $basket->typeLabel() }}</span>
                <h1 class="mt-3 text-3xl font-bold text-stone-900">{{ $basket->name }}</h1>

                @if ($basket->description)
                    <p class="mt-3 text-stone-600">{{ $basket->description }}</p>
                @endif

                <p class="mt-5 text-2xl font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</p>
                <p class="text-xs text-stone-400 mt-1">One-time purchase price for this basket.</p>

                <div class="mt-5 hidden lg:flex items-center gap-3">
                    @if ($inCart)
                        <div class="flex items-center gap-1 bg-brand-600 text-white rounded-xl p-1">
                            <button type="button" wire:click="decrement" wire:loading.attr="disabled" wire:target="decrement" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Decrease quantity"><i data-lucide="minus" class="w-4 h-4"></i></button>
                            <span class="w-8 text-center text-lg font-semibold">
                                <span wire:loading.remove wire:target="increment,decrement">{{ $quantity }}</span>
                                <x-loading-spinner wire:loading wire:target="increment,decrement" class="w-4 h-4 mx-auto" />
                            </span>
                            <button type="button" wire:click="increment" wire:loading.attr="disabled" wire:target="increment" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Increase quantity"><i data-lucide="plus" class="w-4 h-4"></i></button>
                        </div>
                        <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-brand-600 text-brand-600 font-semibold px-4 py-2.5 text-sm hover:bg-brand-50 transition">
                            View Cart
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
                            <span wire:loading.remove.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Add to Cart
                            </span>
                            <span wire:loading.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                                <x-loading-spinner class="w-4 h-4" />
                                Adding...
                            </span>
                        </button>
                    @endif
                </div>

                @if ($cartError)
                    <div class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $cartError }}</div>
                @endif

                <div class="mt-8 border-t border-stone-200 pt-6 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="truck" class="w-5 h-5"></i></span>
                        <p class="text-xs font-medium text-stone-700 mt-2">Free delivery</p>
                        <p class="text-[11px] text-stone-400">Above {{ \Illuminate\Support\Number::currency(config('mart.free_delivery_threshold'), 'INR') }}</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="refresh-ccw" class="w-5 h-5"></i></span>
                        <p class="text-xs font-medium text-stone-700 mt-2">Easy returns</p>
                        <p class="text-[11px] text-stone-400">Within 24 hours</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="shield-check" class="w-5 h-5"></i></span>
                        <p class="text-xs font-medium text-stone-700 mt-2">Quality check</p>
                        <p class="text-[11px] text-stone-400">Before dispatch</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($this->products->isNotEmpty())
            <div class="mt-10 border-t border-stone-200 pt-8">
                <h2 class="text-lg font-semibold text-stone-900 mb-1">What's inside</h2>
                <p class="text-sm text-stone-500 mb-5">Each product priced at its own shop price and unit.</p>

                <div class="bg-white rounded-2xl border border-stone-200 divide-y divide-stone-100">
                    @foreach ($this->products as $product)
                        @php($pivotUnit = $product->units->firstWhere('id', $product->pivot?->product_unit_id))
                        <div class="flex items-center gap-4 p-4">
                            <span class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-stone-800 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-stone-400">{{ $pivotUnit?->unit ?? $product->units->first()?->unit ?? $product->unit }}</p>
                            </div>
                            <p class="text-sm font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($pivotUnit?->price ?? $product->units->first()?->price ?? $product->price, 'INR') }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 lg:hidden flex items-center gap-3">
                    @if ($inCart)
                        <div class="flex items-center gap-1 bg-brand-600 text-white rounded-xl p-1 shrink-0">
                            <button type="button" wire:click="decrement" wire:loading.attr="disabled" wire:target="decrement" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Decrease quantity"><i data-lucide="minus" class="w-4 h-4"></i></button>
                            <span class="w-8 text-center text-lg font-semibold">
                                <span wire:loading.remove wire:target="increment,decrement">{{ $quantity }}</span>
                                <x-loading-spinner wire:loading wire:target="increment,decrement" class="w-4 h-4 mx-auto" />
                            </span>
                            <button type="button" wire:click="increment" wire:loading.attr="disabled" wire:target="increment" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Increase quantity"><i data-lucide="plus" class="w-4 h-4"></i></button>
                        </div>
                        <a href="{{ route('cart') }}" wire:navigate class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-brand-600 text-brand-600 font-semibold px-4 py-3 text-sm hover:bg-brand-50 transition">
                            View Cart
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
                            <span wire:loading.remove.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Add to Cart
                            </span>
                            <span wire:loading.inline-flex wire:target="addToCart" class="inline-flex items-center gap-2">
                                <x-loading-spinner class="w-4 h-4" />
                                Adding...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
