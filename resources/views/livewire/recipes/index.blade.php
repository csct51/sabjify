<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 mb-1">Recipes</h1>
                <p class="text-sm text-stone-500">{{ $recipes->total() }} recipes to try</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" data-reveal>
            @foreach ($recipes as $recipe)
                <a href="{{ route('recipes.show', $recipe) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="p-4 pb-0">
                        <div class="relative aspect-[4/3] overflow-hidden rounded-xl">
                            <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" class="absolute inset-0 w-full h-full {{ $recipe->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                        </div>
                    </div>
                    <div class="p-4">
                        <h2 class="font-semibold text-stone-900 group-hover:text-brand-700">{{ $recipe->title }}</h2>
                        <p class="mt-1 text-xs text-stone-400">{{ $recipe->products_count }} items in this recipe</p>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($recipes->hasPages())
            <div class="mt-10">
                {{ $recipes->links() }}
            </div>
        @endif
    </div>
</div>
