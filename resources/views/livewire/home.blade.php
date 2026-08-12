<div>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="relative bg-brand-600 text-white overflow-hidden rounded-3xl aspect-[16/9]">
        {{-- <div class="absolute inset-0" style="background-image: url('https://images.unsplash.com/photo-1762965619761-e5bee9d18e50?q=80&w=1600&auto=format&fit=crop'); background-size: cover; background-position: center;"></div> --}}
        <div class="absolute inset-0" style="background-image: url('{{ asset('storage/heroes/hero.jpg') }}'); background-size: cover; background-position: center;"></div>
        {{-- <div class="absolute inset-0" style="background-image: url('{{ Storage::url('/heroes/16.jpg') }}'); background-size: cover; background-position: center;"></div> --}}
        <div class="absolute inset-0 bg-gradient-to-r from-stone-950/55 via-stone-950/25 to-transparent"></div>
        <div class="relative grid lg:grid-cols-2 gap-8 items-center h-full px-5 sm:px-10 lg:px-14">
            <div class="max-w-xl">
                <span class="hidden sm:inline-flex items-center gap-1.5 bg-white/15 rounded-full px-3 py-1 text-xs font-medium"><i data-lucide="leaf" class="w-3.5 h-3.5"></i> 100% Farm Fresh</span>
                <h1 class="mt-1 sm:mt-3 text-lg sm:text-3xl lg:text-4xl font-bold leading-tight">Fresh fruits & vegetables delivered to your doorstep</h1>
                <p class="hidden md:block mt-2 text-white/85 text-sm lg:text-base">Handpicked daily from trusted local growers. Order before 10 PM for next-morning delivery.</p>
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
            <div class="hidden lg:flex justify-center gap-4 drop-shadow-lg">
                <i data-lucide="apple" class="w-16 h-16 text-white animate-bounce-slow"></i>
                <i data-lucide="leaf" class="w-16 h-16 text-white animate-bounce-slow [animation-delay:150ms]"></i>
                <i data-lucide="carrot" class="w-16 h-16 text-white animate-bounce-slow [animation-delay:300ms]"></i>
            </div>
        </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12" data-reveal>
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-stone-900">Shop by Category</h2>
            <p class="mt-1 text-sm text-stone-500">Browse our fresh selection</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($this->categories as $category)
                <a href="{{ route('shop', ['category' => $category->slug]) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 p-3 text-center">
                    <div class="relative block w-20 h-20 mx-auto rounded-full overflow-hidden bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                    </div>
                    <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 truncate">{{ $category->name }}</p>
                    <p class="text-xs text-stone-400">{{ $category->products_count }} items</p>
                </a>
            @endforeach
            <a href="{{ route('categories.index') }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 p-3 text-center flex flex-col items-center justify-center">
                <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                    <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                </span>
                <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700">View all</p>
                <p class="text-xs text-stone-400">{{ $this->categories->sum('products_count') }} items</p>
            </a>
        </div>
    </section>

    @if ($this->recipes->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-stone-900">Recipes</h2>
                <p class="mt-1 text-sm text-stone-500">Simple meals made fresh</p>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($this->recipes as $recipe)
                    <a href="{{ route('recipes.show', $recipe) }}" wire:navigate class="group bg-white rounded-xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <div class="p-2.5 pb-0">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-lg">
                                <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" class="absolute inset-0 w-full h-full {{ $recipe->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                            </div>
                        </div>
                        <div class="p-2.5">
                            <h3 class="text-xs font-medium text-stone-800 leading-snug group-hover:text-brand-700 line-clamp-1">{{ $recipe->title }}</h3>
                            <p class="mt-0.5 text-[10px] text-stone-400">{{ $recipe->products_count }} items</p>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('recipes.index') }}" wire:navigate class="group bg-white rounded-xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-2.5">
                    <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                    </span>
                    <p class="mt-2 text-xs font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                </a>
            </div>
        </section>
    @endif

    @if ($this->wellnessBaskets->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-stone-900">Wellness Baskets</h2>
                <p class="mt-1 text-sm text-stone-500">Curated for a healthier you</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($this->wellnessBaskets as $basket)
                    <a href="{{ route('baskets.show', $basket) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <div class="p-3 pb-0">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-xl">
                                <img src="{{ $basket->displayImageUrl() }}" alt="{{ $basket->name }}" class="absolute inset-0 w-full h-full {{ $basket->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-medium text-stone-800 leading-snug group-hover:text-brand-700 line-clamp-1">{{ $basket->name }}</h3>
                            <p class="mt-1 text-sm font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</p>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('baskets.index') }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-3">
                    <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                    </span>
                    <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                </a>
            </div>
        </section>
    @endif

    @if ($this->sabjifyBaskets->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16" data-reveal>
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-stone-900">Sabjify Baskets</h2>
                <p class="mt-1 text-sm text-stone-500">Our signature daily baskets</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($this->sabjifyBaskets as $basket)
                    <a href="{{ route('baskets.show', $basket) }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                        <div class="p-3 pb-0">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-xl">
                                <img src="{{ $basket->displayImageUrl() }}" alt="{{ $basket->name }}" class="absolute inset-0 w-full h-full {{ $basket->imageFit() }} transition-transform duration-300 group-hover:scale-105">
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-medium text-stone-800 leading-snug group-hover:text-brand-700 line-clamp-1">{{ $basket->name }}</h3>
                            <p class="mt-1 text-sm font-semibold text-stone-900">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</p>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('baskets.index') }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:border-brand-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col items-center justify-center p-3">
                    <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                        <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                    </span>
                    <p class="mt-2 text-sm font-medium text-stone-800 group-hover:text-brand-700 text-center">View all</p>
                </a>
            </div>
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16" data-reveal>
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-stone-900">Featured Products</h2>
            <p class="mt-1 text-sm text-stone-500">Handpicked favourites just for you</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($this->featuredProducts as $product)
                <livewire:product-card :product="$product" :key="'featured-'.$product->id" />
            @endforeach
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16" data-reveal>
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-stone-900">New Arrivals</h2>
            <p class="mt-1 text-sm text-stone-500">Just landed in our store</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($this->newArrivals as $product)
                <livewire:product-card :product="$product" :key="'new-'.$product->id" />
            @endforeach
            <a href="{{ route('shop') }}" wire:navigate class="group bg-white rounded-2xl border border-stone-200 hover:border-brand-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden flex flex-col items-center justify-center p-3">
                <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-50 to-lime-100 ring-1 ring-stone-100 group-hover:ring-brand-300 transition">
                    <i data-lucide="arrow-right" class="w-8 h-8 text-brand-600"></i>
                </span>
                <p class="mt-2 text-sm font-semibold text-stone-800 group-hover:text-brand-700 text-center">View all</p>
            </a>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-4" data-reveal>
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
    </section>
</div>
