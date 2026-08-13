<div class="bg-white/70 backdrop-blur-md border-b border-stone-200/80 sticky top-0 z-40 supports-[backdrop-filter]:bg-white/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                <img src="{{ asset('logo.svg') }}" alt="Sabjify" class="h-16 w-auto">
            </a>

            <div class="flex items-center">
                <a href="{{ route('search') }}" wire:navigate aria-label="Search" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </a>

                @auth
                    <a href="{{ route('profile') }}" wire:navigate aria-label="Profile" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition {{ request()->routeIs('profile') ? 'text-brand-600' : '' }}">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate aria-label="Login" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
