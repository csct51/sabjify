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

                <div class="mt-6 border-t border-stone-200 pt-6">
                    <h2 class="text-lg font-semibold text-stone-900 mb-3">Products in this recipe</h2>
                    <p class="text-sm text-stone-500 mb-4">{{ $this->products->count() }} products · all available in our shop</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @forelse ($this->products as $product)
                            <a href="{{ route('product.show', $product) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 p-3 hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-brand-50 to-lime-100">
                                    <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                </div>
                                <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-stone-400">{{ $product->category?->name }}</p>
                                <p class="mt-1 text-sm font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($product->price, 'INR') }}<span class="text-xs font-normal text-stone-400"> / {{ $product->unit }}</span></p>
                            </a>
                        @empty
                            <p class="text-stone-400 col-span-full">No products linked to this recipe yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
