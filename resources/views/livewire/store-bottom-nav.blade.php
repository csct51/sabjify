<div class="fixed bottom-0 inset-x-0 z-40 lg:hidden">
    <div class="bg-white border-t border-stone-200 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
        <nav class="max-w-lg mx-auto grid grid-cols-4">
            <a href="{{ route('home') }}" wire:navigate class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('home') ? 'text-brand-600' : 'text-stone-500 hover:text-stone-700' }}">
                <span class="relative">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    @if (request()->routeIs('home'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-0.5 rounded-full bg-brand-600"></span>
                    @endif
                </span>
                <span class="text-[10px] font-medium">Home</span>
            </a>

            <a href="{{ route('cart') }}" wire:navigate class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('cart') ? 'text-brand-600' : 'text-stone-500 hover:text-stone-700' }}">
                <span class="relative">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    @if ($this->cartCount > 0)
                        <span class="absolute -top-1.5 -right-2 min-w-4 h-4 px-1 rounded-full bg-brand-600 text-white text-[10px] font-semibold flex items-center justify-center">{{ $this->cartCount }}</span>
                    @endif
                    @if (request()->routeIs('cart'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-0.5 rounded-full bg-brand-600"></span>
                    @endif
                </span>
                <span class="text-[10px] font-medium">Cart</span>
            </a>

            <a href="{{ route('orders.index') }}" wire:navigate class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('orders.*') ? 'text-brand-600' : 'text-stone-500 hover:text-stone-700' }}">
                <span class="relative">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    @if (request()->routeIs('orders.*'))
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-0.5 rounded-full bg-brand-600"></span>
                    @endif
                </span>
                <span class="text-[10px] font-medium">Orders</span>
            </a>

            @auth
                <a href="{{ route('profile') }}" wire:navigate class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('profile') ? 'text-brand-600' : 'text-stone-500 hover:text-stone-700' }}">
                    <span class="relative">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        @if (request()->routeIs('profile'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-0.5 rounded-full bg-brand-600"></span>
                        @endif
                    </span>
                    <span class="text-[10px] font-medium">Profile</span>
                </a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="flex flex-col items-center gap-1 py-2.5 {{ request()->routeIs('login') ? 'text-brand-600' : 'text-stone-500 hover:text-stone-700' }}">
                    <span class="relative">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        @if (request()->routeIs('login'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-0.5 rounded-full bg-brand-600"></span>
                        @endif
                    </span>
                    <span class="text-[10px] font-medium">Login</span>
                </a>
            @endauth
        </nav>
    </div>
</div>
