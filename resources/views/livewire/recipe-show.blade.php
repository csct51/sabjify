<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-stone-400 mb-6 flex items-center gap-1.5 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-600">Home</a>
            <span>/</span>
            <a href="{{ route('recipes.index') }}" wire:navigate class="hover:text-brand-600">Recipes</a>
            <span>/</span>
            <span class="text-stone-600 font-medium">{{ $recipe->title }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10" data-reveal>
            <div class="relative bg-gradient-to-br from-brand-50 to-lime-100 rounded-3xl border border-stone-200 aspect-[4/3] overflow-hidden">
                <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" class="absolute inset-0 w-full h-full object-cover">
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1">
                    <i data-lucide="utensils" class="w-3.5 h-3.5"></i>
                    Recipe
                </span>

                <h1 class="mt-3 text-3xl font-bold text-stone-900">{{ $recipe->title }}</h1>

                @if ($recipe->description)
                    <p class="mt-3 text-stone-600 leading-relaxed">{{ $recipe->description }}</p>
                @endif

                @if ($this->products->isNotEmpty())
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="addAllToCart" wire:loading.attr="disabled" wire:target="addAllToCart" class="hidden lg:inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70">
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

                <div class="mt-8 flex flex-wrap gap-3">
                    <div class="flex items-center gap-2 rounded-xl bg-white border border-stone-200 px-4 py-2.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="list-checks" class="w-4 h-4"></i></span>
                        <div>
                            <p class="text-sm font-semibold text-stone-800">{{ $this->products->count() }}</p>
                            <p class="text-[11px] text-stone-400">ingredients</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl bg-white border border-stone-200 px-4 py-2.5">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="utensils" class="w-4 h-4"></i></span>
                        <div>
                            <p class="text-sm font-semibold text-stone-800">{{ count($recipe->steps ?? []) }}</p>
                            <p class="text-[11px] text-stone-400">steps</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (! empty($recipe->steps))
            <div class="mt-12 border-t border-stone-200 pt-8">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 text-white"><i data-lucide="chef-hat" class="w-5 h-5"></i></span>
                    <h2 class="text-xl font-bold text-stone-900">How to make</h2>
                </div>

                <ol class="mt-6 space-y-4">
                    @foreach ($recipe->steps as $index => $step)
                        <li class="flex items-start gap-4">
                            <span class="flex items-center justify-center w-9 h-9 shrink-0 rounded-full bg-brand-600 text-white font-semibold">{{ $index + 1 }}</span>
                            <p class="flex-1 pt-1.5 text-stone-700 leading-relaxed">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if ($this->products->isNotEmpty())
            <div class="mt-10 border-t border-stone-200 pt-8">
                <h2 class="text-lg font-semibold text-stone-900 mb-1">Ingredients</h2>
                <p class="text-sm text-stone-500 mb-5">{{ $this->products->count() }} items · everything you need, available in our shop</p>

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
