<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 mb-1">Recipes</h1>
                <p class="text-sm text-stone-500">{{ $this->totalRecipes() }} recipes to try</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" data-reveal>
            @forelse ($this->items as $recipe)
                <a href="{{ route('recipes.show', $recipe) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="p-4 pb-0">
                        <div class="relative aspect-[4/3] overflow-hidden rounded-xl">
                            <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full {{ $recipe->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                        </div>
                    </div>
                    <div class="p-4">
                        <h2 class="font-semibold text-stone-900 group-hover:text-brand-700">{{ $recipe->title }}</h2>
                        @if ($recipe->description)
                            <p class="mt-1.5 text-sm text-stone-500 leading-relaxed line-clamp-3">{{ $recipe->description }}</p>
                        @endif
                        <p class="mt-2 text-xs text-stone-400">{{ $recipe->products_count }} items in this recipe</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-20">
                    <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="chef-hat" class="w-8 h-8"></i></span>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900">No recipes found</h3>
                    <p class="text-sm text-stone-500 mt-1">We couldn't find any recipes to show right now.</p>
                </div>
            @endforelse
        </div>

        @if ($this->hasMore)
            <div
                x-intersect.full.margin.0px.0px.200px="$wire.loadMore()"
                class="mt-10 flex justify-center"
            >
                <x-loading-spinner wire:loading wire:target="loadMore" class="w-6 h-6 text-brand-600" />
            </div>

            <div class="mt-4 flex justify-center" wire:loading.remove wire:target="loadMore">
                <button type="button" wire:click="loadMore" class="rounded-xl border border-stone-300 text-stone-600 px-5 py-2.5 text-sm font-medium hover:bg-stone-50 transition">
                    Load more
                </button>
            </div>
        @endif
    </div>
</div>
