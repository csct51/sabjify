<div class="bg-white border-b border-stone-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 text-white shadow-sm"><i data-lucide="leaf" class="w-5 h-5"></i></span>
                <span class="font-bold text-lg text-stone-900">{{ config('app.name') }}</span>
            </a>

            <form action="{{ route('shop') }}" method="GET" class="flex-1 max-w-md mx-auto hidden sm:block">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search fruits & vegetables..."
                        class="w-full rounded-full border-stone-200 bg-stone-50 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>
            </form>

            <form action="{{ route('shop') }}" method="GET" class="sm:hidden flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search..."
                        class="w-full rounded-full border-stone-200 bg-stone-50 py-2 pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('cart') }}" wire:navigate class="hidden sm:inline-flex relative items-center justify-center w-10 h-10 rounded-lg hover:bg-stone-100 text-stone-700" aria-label="Cart">
                    <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                    @if ($this->cartCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 rounded-full bg-brand-600 text-white text-xs font-semibold flex items-center justify-center">{{ $this->cartCount }}</span>
                    @endif
                </a>

                @auth
                    <div x-data="{ open: false }" class="relative hidden sm:block">
                        <button type="button" @click="open = !open" class="inline-flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-stone-100">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-semibold text-sm">{{ auth()->user()->initials() }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400"></i>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-stone-200 shadow-lg py-1 text-sm" x-transition>
                            <div class="px-4 py-2 border-b border-stone-100">
                                <p class="font-medium text-stone-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-stone-400">{{ auth()->user()->phone }}</p>
                            </div>
                            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 hover:bg-stone-50 text-stone-600">My Profile</a>
                            <a href="{{ route('orders.index') }}" wire:navigate class="block px-4 py-2 hover:bg-stone-50 text-stone-600">My Orders</a>
                            <button type="button" wire:click="logout" class="block w-full text-left px-4 py-2 hover:bg-stone-50 text-red-600">Logout</button>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Login</a>
                @endauth
            </div>
        </div>
    </div>
</div>
