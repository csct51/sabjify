<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-stone-400 mb-6 flex items-center gap-1.5 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600">Home</a>
            <span>/</span>
            <a href="{{ route('shop') }}" wire:navigate class="hover:text-brand-600">Shop</a>
            <span>/</span>
            <a href="{{ route('shop', ['category' => $product->category?->slug]) }}" wire:navigate class="hover:text-brand-600">{{ $product->category?->name }}</a>
            <span>/</span>
            <span class="text-stone-600 font-medium">{{ $product->name }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10" data-reveal>
            <div class="relative bg-gradient-to-br from-brand-50 to-lime-100 rounded-3xl border border-stone-200 aspect-square overflow-hidden">
                <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 hover:scale-105">
            </div>

            <div>
                <p class="text-sm uppercase tracking-wide text-brand-600 font-semibold">{{ $product->category?->name }}</p>
                <h1 class="mt-2 text-3xl font-bold text-stone-900">{{ $product->name }}</h1>
                <p class="text-sm text-stone-500 mt-1">Price per {{ $product->unit }}</p>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-3xl font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($product->price, 'INR') }}</span>
                    @if ($product->mrp && $product->mrp > $product->price)
                        <span class="text-lg text-stone-400 line-through">{{ \Illuminate\Support\Number::currency($product->mrp, 'INR') }}</span>
                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-lg">{{ $product->discountPercent() }}% OFF</span>
                    @endif
                </div>

                <div class="mt-3 flex items-center gap-4 text-sm">
                    @if ($product->inStock())
                        <span class="inline-flex items-center gap-1.5 text-green-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> In Stock
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-red-500 font-medium">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Out of Stock
                        </span>
                    @endif
                    <span class="text-stone-400">SKU: {{ $product->id }}</span>
                </div>

                <p class="mt-6 text-stone-600 leading-relaxed">{{ $product->description }}</p>

                @if ($product->inStock())
                    <div class="mt-8">
                        @if ($this->inCart)
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center gap-1 bg-brand-600 text-white rounded-xl p-1">
                                    <button type="button" wire:click="decrement" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Decrease quantity"><i data-lucide="minus" class="w-4 h-4"></i></button>
                                    <span class="w-8 text-center text-lg font-semibold">{{ $quantity }}</span>
                                    <button type="button" wire:click="increment" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-brand-700" aria-label="Increase quantity"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                </div>
                                <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    View Cart →
                                </a>
                            </div>
                        @else
                            <button type="button" wire:click="addToCart" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-8 py-3 transition">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Add to Cart
                            </button>
                        @endif

                        @error('stock')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mt-10 border-t border-stone-200 pt-6 grid grid-cols-3 gap-4 text-center">
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

        @if ($this->relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="text-2xl font-bold text-stone-900 mb-6">You might also like</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($this->relatedProducts as $related)
                        <livewire:product-card :product="$related" :key="'related-'.$related->id" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
