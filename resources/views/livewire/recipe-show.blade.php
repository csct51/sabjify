<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-stone-400 mb-6 flex items-center gap-1.5 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600">Home</a>
            <span>/</span>
            <span class="text-stone-600 font-medium">Recipes</span>
            <span>/</span>
            <span class="text-stone-600 font-medium">{{ $recipe->title }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10" data-reveal>
            <div class="relative bg-gradient-to-br from-brand-50 to-lime-100 rounded-3xl border border-stone-200 aspect-[4/3] overflow-hidden">
                <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" class="absolute inset-0 w-full h-full object-cover">
            </div>

            <div>
                <h1 class="text-3xl font-bold text-stone-900">{{ $recipe->title }}</h1>

                @if ($recipe->description)
                    <p class="mt-3 text-stone-600 leading-relaxed">{{ $recipe->description }}</p>
                @endif

                @if ($this->products->isNotEmpty())
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="addAllToCart" wire:loading.attr="disabled" wire:target="addAllToCart" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
                            <span wire:loading.remove wire:target="addAllToCart"><i data-lucide="shopping-cart" class="w-5 h-5"></i></span>
                            <x-loading-spinner wire:loading wire:target="addAllToCart" class="w-4 h-4" />
                            <span wire:loading.remove wire:target="addAllToCart">Add All to Cart</span>
                            <span wire:loading wire:target="addAllToCart">Adding...</span>
                        </button>
                        <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            View Cart →
                        </a>
                    </div>
                @endif

                @if ($cartMessage)
                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ $cartMessage }}</div>
                @endif
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
                <h2 class="text-lg font-semibold text-stone-900 mb-3">Products in this recipe</h2>
                <p class="text-sm text-stone-500 mb-5">{{ $this->products->count() }} products · all available in our shop</p>

                <div class="bg-white rounded-2xl border border-stone-200 divide-y divide-stone-100">
                    @foreach ($this->products as $product)
                        <livewire:recipe-product :recipe="$recipe" :product="$product" :key="'recipe-product-'.$product->id" />
                    @endforeach
                </div>

                <div class="mt-8 lg:hidden">
                    <button type="button" wire:click="addAllToCart" wire:loading.attr="disabled" wire:target="addAllToCart" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
                        <span wire:loading.remove wire:target="addAllToCart"><i data-lucide="shopping-cart" class="w-5 h-5"></i></span>
                        <x-loading-spinner wire:loading wire:target="addAllToCart" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="addAllToCart">Add All to Cart</span>
                        <span wire:loading wire:target="addAllToCart">Adding...</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
