<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 mb-1">Baskets</h1>
            <p class="text-sm text-stone-500">{{ $wellnessBaskets->count() + $sabjifyBaskets->count() }} curated baskets to explore</p>
        </div>
    </div>

    @if ($wellnessBaskets->isNotEmpty() || $sabjifyBaskets->isNotEmpty())
        @if ($wellnessBaskets->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
                <div class="flex items-center gap-2.5 mb-4">
                    <h2 class="text-lg font-semibold text-stone-900">Wellness Baskets</h2>
                    <span class="inline-flex items-center rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-2.5 py-0.5">{{ $wellnessBaskets->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 items-start" data-reveal>
                    @foreach ($wellnessBaskets as $basket)
                        <livewire:basket-card :basket="$basket" :key="'wellness-'.$basket->id" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($sabjifyBaskets->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
                <div class="flex items-center gap-2.5 mb-4">
                    <h2 class="text-lg font-semibold text-stone-900">Sabjify Baskets</h2>
                    <span class="inline-flex items-center rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-2.5 py-0.5">{{ $sabjifyBaskets->count() }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 items-start" data-reveal>
                    @foreach ($sabjifyBaskets as $basket)
                        <livewire:basket-card :basket="$basket" :key="'sabjify-'.$basket->id" />
                    @endforeach
                </div>
            </section>
        @endif
    @else
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="rounded-2xl border border-dashed border-stone-300 p-14 text-center">
                <p class="text-stone-400">No baskets available right now. Check back soon!</p>
            </div>
        </div>
    @endif
</div>
