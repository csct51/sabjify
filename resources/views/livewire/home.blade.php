<div>
    <section class="md:hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <img src="{{ asset('storage/heroes/2.jpg') }}" alt="Fresh fruits and vegetables" class="w-full h-auto block rounded-3xl">
    </section>

    <section class="hidden md:block max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="relative bg-brand-600 text-white overflow-hidden rounded-3xl">
            <img src="{{ asset('storage/heroes/hero09.jpg') }}" alt="Fresh fruits and vegetables" class="w-full h-auto block">
            <div class="absolute inset-0 bg-gradient-to-r from-stone-950/55 via-stone-950/25 to-transparent"></div>
            <div class="absolute inset-0 flex items-center px-5 sm:px-10 lg:px-14">
                <div class="max-w-xl">
                    <span class="hidden sm:inline-flex items-center gap-1.5 bg-white/15 rounded-full px-3 py-1 text-xs font-medium"><i data-lucide="leaf" class="w-3.5 h-3.5"></i> 100% Farm Fresh</span>
                    <h1 class="mt-1 sm:mt-3 text-lg sm:text-3xl lg:text-4xl font-bold leading-tight">Fresh fruits & vegetables delivered to your doorstep</h1>
                    <div class="mt-4 sm:mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('shop') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-white text-brand-700 px-4 sm:px-5 py-2 sm:py-2.5 text-sm font-semibold hover:bg-brand-50 transition hover:scale-[1.03] active:scale-95">
                            Shop Now
                            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-0.5"></i>
                        </a>
                        <a href="{{ route('categories.index') }}" wire:navigate class="hidden md:inline-flex items-center gap-2 rounded-xl border-2 border-white/60 px-5 py-2.5 text-sm font-semibold hover:bg-white/10 transition hover:scale-[1.03] active:scale-95">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            Browse Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-extrabold text-stone-900 font-heading">Shop by Category</h2>
                <p class="mt-1 text-sm text-stone-500">Explore fresh produce and daily essentials, organised by category.</p>
            </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($this->categories as $category)
                <a href="{{ route('shop', ['category' => $category->slug]) }}" wire:navigate class="group text-center">
                    <div class="relative w-full aspect-square overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50 to-lime-100 group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-300">
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 truncate">{{ $category->name }}</p>
                </a>
            @endforeach
            <a href="{{ route('categories.index') }}" wire:navigate class="group text-center flex flex-col items-center justify-center">
                <div class="relative w-full aspect-square rounded-2xl bg-gradient-to-br from-brand-50 to-lime-100 inline-flex items-center justify-center group-hover:shadow-md group-hover:-translate-y-0.5 transition-all duration-300">
                    <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                </div>
                <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700">View all</p>
            </a>
            </div>
        </div>
    </section>

    @if ($this->sabjifyBaskets->isNotEmpty())
        <section class="bg-stone-50 py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-extrabold text-stone-900 font-heading">Sabjify Baskets</h2>
                    <p class="mt-1 text-sm text-stone-500">Ready-to-cook daily vegetable baskets, delivered straight to your door.</p>
                </div>
                <div class="flex gap-4 overflow-x-auto py-2 snap-x snap-mandatory no-scrollbar">
                    @foreach ($this->sabjifyBaskets as $basket)
                        <div class="shrink-0 snap-start w-64 sm:w-72">
                            <livewire:basket-card :basket="$basket" :show-items="true" :key="'sabjify-'.$basket->id" />
                        </div>
                    @endforeach
                    <a href="{{ route('baskets.index') }}" wire:navigate class="group shrink-0 snap-start w-64 sm:w-72 bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-3">
                        <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                            <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                        </span>
                        <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                    </a>
                </div>
                </div>
            </section>
    @endif

    @if ($this->wellnessBaskets->isNotEmpty())
        <section class="bg-brand-50/50 py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-extrabold text-stone-900 font-heading">Wellness Baskets</h2>
                    <p class="mt-1 text-sm text-stone-500">Balanced, nutrient-rich baskets curated to support a healthier everyday.</p>
                </div>
                <div class="flex gap-4 overflow-x-auto py-2 snap-x snap-mandatory no-scrollbar">
                    @foreach ($this->wellnessBaskets as $basket)
                        <div class="shrink-0 snap-start w-64 sm:w-72">
                            <livewire:basket-card :basket="$basket" :key="'wellness-'.$basket->id" />
                        </div>
                    @endforeach
                    <a href="{{ route('baskets.index') }}" wire:navigate class="group shrink-0 snap-start w-64 sm:w-72 bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-3">
                        <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                            <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                        </span>
                        <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                    </a>
                </div>
                </div>
            </section>
    @endif

    <section class="bg-white py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-extrabold text-stone-900 font-heading">Featured Products</h2>
                <p class="mt-1 text-sm text-stone-500">A handpicked mix of seasonal favourites and bestsellers our customers love.</p>
            </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse ($this->featuredProducts as $product)
                <livewire:product-card :product="$product" :key="'featured-'.$product->id" />
            @empty
                <div class="col-span-full text-center py-12">
                    <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="package-open" class="w-8 h-8"></i></span>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900">No products available right now</h3>
                    <p class="text-sm text-stone-500 mt-1">Check back soon for fresh arrivals.</p>
                    <a href="{{ route('shop') }}" wire:navigate class="mt-4 inline-block rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Shop all</a>
                </div>
            @endforelse
            </div>
        </div>
    </section>

    <section class="bg-stone-50 py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-extrabold text-stone-900 font-heading">Recipes</h2>
                <p class="mt-1 text-sm text-stone-500">Easy, home-style recipes you can cook with the fresh produce you buy.</p>
            </div>
        @if ($this->recipes->isEmpty())
            <div class="text-center py-16">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="chef-hat" class="w-8 h-8"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-stone-900">No recipes yet</h3>
                <p class="text-sm text-stone-500 mt-1">We're cooking up something fresh. Check back soon!</p>
                <a href="{{ route('recipes.index') }}" wire:navigate class="mt-4 inline-block rounded-xl bg-brand-600 text-white px-5 py-2.5 text-sm font-semibold hover:bg-brand-700">Browse recipes</a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($this->recipes as $recipe)
                    <a href="{{ route('recipes.show', $recipe) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <div class="p-3 pb-0">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-xl">
                                <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full {{ $recipe->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-stone-800 leading-snug group-hover:text-brand-700 line-clamp-1">{{ $recipe->title }}</h3>
                            @if ($recipe->description)
                                <p class="mt-1.5 text-xs text-stone-500 leading-relaxed line-clamp-3">{{ $recipe->description }}</p>
                            @endif
                            <p class="mt-2 text-[11px] font-medium text-stone-400">{{ $recipe->products_count }} items</p>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('recipes.index') }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-6">
                    <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                    </span>
                    <p class="mt-3 text-sm font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                </a>
            </div>
        @endif
        </div>
    </section>

    <section class="bg-brand-50/40 py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" data-reveal>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="truck" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">Fast Delivery</p>
                <p class="text-xs text-stone-400">Same-day slots</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="badge-check" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">Quality Checked</p>
                <p class="text-xs text-stone-400">Handpicked daily</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="indian-rupee" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">Best Prices</p>
                <p class="text-xs text-stone-400">Direct from farms</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="refresh-ccw" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">Easy Returns</p>
                <p class="text-xs text-stone-400">No questions asked</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="lock" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">Secure Payments</p>
                <p class="text-xs text-stone-400">COD & Online</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="headset" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">24x7 Support</p>
                <p class="text-xs text-stone-400">We're here to help</p>
            </div>
            </div>
        </div>
    </section>
</div>
