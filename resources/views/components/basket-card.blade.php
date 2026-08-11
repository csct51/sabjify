@props(['basket'])

<a href="{{ route('baskets.show', $basket) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
    <div class="relative aspect-[4/3] bg-gradient-to-br from-brand-50 to-lime-100 overflow-hidden">
        <img src="{{ $basket->displayImageUrl() }}" alt="{{ $basket->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
        <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-white/90 text-brand-700 text-xs font-semibold px-2.5 py-1 shadow">{{ $basket->typeLabel() }}</span>
    </div>
    <div class="p-4">
        <h2 class="font-semibold text-stone-900 group-hover:text-brand-700 line-clamp-1">{{ $basket->name }}</h2>
        <p class="mt-1 text-xs text-stone-400">{{ $basket->products_count }} items inside</p>
        <p class="mt-2 font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</p>
    </div>
</a>
