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

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
                        <span wire:loading.remove wire:target="addToCart"><i data-lucide="shopping-cart" class="w-5 h-5"></i></span>
                        <x-loading-spinner wire:loading wire:target="addToCart" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="addToCart">Add to Cart</span>
                        <span wire:loading wire:target="addToCart">Adding...</span>
                    </button>
                    <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        View Cart →
                    </a>
                </div>

                @if ($cartMessage)
                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ $cartMessage }}</div>
                @endif
                @if ($cartError)
                    <div class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $cartError }}</div>
                @endif

                @if ($this->products->isNotEmpty())
                    <div class="mt-6 border-t border-stone-200 pt-6">
                        <h2 class="text-lg font-semibold text-stone-900 mb-1">What's inside</h2>
                        <p class="text-sm text-stone-500 mb-4">Contents and their indicative packaging. Individual product prices in the shop are unchanged.</p>

                        <div class="divide-y divide-stone-100 border border-stone-200 rounded-2xl overflow-hidden">
                            @foreach ($this->products as $product)
                                <div class="flex items-center gap-3 p-3 bg-white">
                                    <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-stone-800 truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-stone-400">{{ $product->pivot->unit ?? $product->unit }}</p>
                                    </div>
                                    @if ($product->pivot->price !== null)
                                        <p class="text-sm font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($product->pivot->price, 'INR') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
