<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-center" data-reveal>
        <div class="relative mx-auto w-full bg-gradient-to-br from-brand-50 to-lime-100 rounded-3xl border border-stone-200 aspect-[16/9] overflow-hidden">
            <img src="{{ str_replace('/storage/', '/public/storage/', $recipe->displayImageUrl()) }}" alt="{{ $recipe->title }}" class="absolute inset-0 w-full h-full object-cover">
        </div>

        <div class="mt-6 flex justify-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1">
                <i data-lucide="utensils" class="w-3.5 h-3.5"></i>
                Recipe
            </span>
        </div>

        <h1 class="mt-3 text-3xl font-bold text-stone-900">{{ $recipe->title }}</h1>

        @if ($recipe->description)
            <p class="mt-3 text-stone-600 leading-relaxed">{{ $recipe->description }}</p>
        @endif

        @if ($this->products->isNotEmpty())
            <div x-data="{ open: false }" class="mt-8 text-left">
                <button type="button" @click="open = ! open" class="flex items-center justify-between w-full rounded-2xl bg-white border border-stone-200 px-4 py-3 font-semibold text-stone-900">
                    <span class="flex items-center gap-2">
                        <i data-lucide="list-checks" class="w-4 h-4 text-brand-600"></i>
                        Ingredients ({{ $this->products->count() }})
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-stone-500 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" class="mt-3">
                    <div class="bg-white rounded-2xl border border-stone-200 divide-y divide-stone-100">
                        @foreach ($this->products as $product)
                            <livewire:recipe-product :recipe="$recipe" :product="$product" :key="'recipe-product-'.$product->id" />
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        <button type="button" wire:click="addAllToCart" wire:loading.attr="disabled" wire:target="addAllToCart" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition active:scale-95 disabled:opacity-70 w-full sm:w-auto">
                            <span wire:loading.remove wire:target="addAllToCart"><i data-lucide="shopping-cart" class="w-5 h-5"></i></span>
                            <x-loading-spinner wire:loading wire:target="addAllToCart" class="w-4 h-4" />
                            <span wire:loading.remove wire:target="addAllToCart">Add All to Cart</span>
                            <span wire:loading wire:target="addAllToCart">Adding...</span>
                        </button>
                        <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            View Cart →
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if (! empty($recipe->steps))
            <div class="mt-10 text-left">
                <div class="flex items-center justify-center gap-2">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 text-white"><i data-lucide="chef-hat" class="w-5 h-5"></i></span>
                    <h2 class="text-xl font-bold text-stone-900">How to make ({{ count($recipe->steps) }} steps)</h2>
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
    </div>
</div>
