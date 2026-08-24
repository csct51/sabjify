<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 mb-1">Categories</h1>
                <p class="text-sm text-stone-500">{{ $categories->total() }} categories to explore</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="grid grid-cols-3 lg:grid-cols-4 gap-4" data-reveal>
            @foreach ($categories as $category)
                <a href="{{ route('shop', ['category' => $category->slug]) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 p-4 text-center hover:border-brand-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <div class="relative block w-full aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <h2 class="mt-3 font-semibold text-stone-900 group-hover:text-brand-700 truncate">{{ $category->name }}</h2>
                    <p class="mt-1 text-xs text-stone-400">{{ $category->products_count }} items</p>
                </a>
            @endforeach
        </div>

        @if ($categories->hasPages())
            <div class="mt-10">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
