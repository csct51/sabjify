<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="/favicon.png">

        <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name') }} Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-stone-100 text-stone-900 antialiased min-h-screen" x-data="{ sidebarOpen: false }">
        <div class="flex min-h-screen">
            @php
                $topItems = [
                    ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                    ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'label' => 'Orders', 'icon' => 'package'],
                ];
                $groups = [
                    [
                        'label' => 'Catalog',
                        'items' => [
                            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Categories', 'icon' => 'folder'],
                            ['route' => 'admin.units.index', 'match' => 'admin.units.*', 'label' => 'Units', 'icon' => 'scale'],
                            ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'label' => 'Products', 'icon' => 'shopping-basket'],
                            ['route' => 'admin.recipes.index', 'match' => 'admin.recipes.*', 'label' => 'Recipes', 'icon' => 'book-open'],
                            ['route' => 'admin.baskets.index', 'match' => 'admin.baskets.*', 'label' => 'Baskets', 'icon' => 'gift'],
                        ],
                    ],
                    [
                        'label' => 'Inventory',
                        'items' => [
                            ['route' => 'admin.purchases.index', 'match' => 'admin.purchases.*', 'label' => 'Purchases', 'icon' => 'boxes'],
                            ['route' => 'admin.wastages.index', 'match' => 'admin.wastages.*', 'label' => 'Wastage', 'icon' => 'trash-2'],
                            ['route' => 'admin.suppliers.index', 'match' => 'admin.suppliers.*', 'label' => 'Suppliers', 'icon' => 'truck'],
                        ],
                    ],
                    [
                        'label' => 'Operations',
                        'items' => [
                            ['route' => 'admin.prices', 'match' => 'admin.prices*', 'label' => 'Prices', 'icon' => 'indian-rupee'],
                            ['route' => 'admin.delivery-locations.index', 'match' => 'admin.delivery-locations.*', 'label' => 'Delivery Locations', 'icon' => 'navigation'],
                        ],
                    ],
                    [
                        'label' => 'Reports',
                        'items' => [
                            ['route' => 'admin.reports.stock', 'match' => 'admin.reports.*', 'label' => 'Stock Quantity', 'icon' => 'clipboard-list'],
                            ['route' => 'admin.reports.selling', 'match' => 'admin.reports.*', 'label' => 'Selling Report', 'icon' => 'banknote'],
                            ['route' => 'admin.reports.purchases', 'match' => 'admin.reports.*', 'label' => 'Purchase Report', 'icon' => 'boxes'],
                            ['route' => 'admin.reports.wastage', 'match' => 'admin.reports.*', 'label' => 'Wastage Report', 'icon' => 'trash-2'],
                        ],
                    ],
                    [
                        'label' => 'Customers',
                        'items' => [
                            ['route' => 'admin.customers.index', 'match' => 'admin.customers.*', 'label' => 'Customers', 'icon' => 'users'],
                        ],
                    ],
                ];
            @endphp

            <aside class="hidden lg:flex flex-col w-64 bg-stone-900 text-stone-300 shrink-0 fixed top-0 bottom-0 left-0">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 px-5 h-16 border-b border-stone-800">
                                <x-logo class="w-9 h-9 rounded-xl" />
                    <div>
                        <p class="font-bold text-white leading-tight">{{ config('app.name') }}</p>
                        <p class="text-[11px] text-stone-400">Admin Panel</p>
                    </div>
                </a>

                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-4 text-sm font-medium">
                    <div class="space-y-1">
                        @foreach ($topItems as $item)
                            @php $itemActive = str_ends_with($item['route'], '.index') ? \Illuminate\Support\Str::beforeLast($item['route'], '.').'.*' : $item['route']; @endphp
                            <a href="{{ route($item['route']) }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($itemActive) ? 'bg-brand-600 text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                    @foreach ($groups as $group)
                        @php
                            $isGroupActive = collect($group['items'])->contains(fn($i) => request()->routeIs($i['match']));
                            $groupPaths = collect($group['items'])->map(fn($i) => parse_url(route($i['route']), PHP_URL_PATH))->values()->all();
                        @endphp
                        <div x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }" data-prefixes='@json($groupPaths)' @@livewire:navigated.window="open = JSON.parse($el.dataset.prefixes).some(p => window.location.pathname.startsWith(p)) || open">
                            <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-3 py-1.5 text-[11px] uppercase tracking-wide text-stone-400 hover:text-stone-300">
                                <span>{{ $group['label'] }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="open ? '' : '-rotate-90'"></i>
                            </button>
                            <div x-show="open" class="mt-1 space-y-1">
                                @foreach ($group['items'] as $item)
                                    @php $itemActive = str_ends_with($item['route'], '.index') ? \Illuminate\Support\Str::beforeLast($item['route'], '.').'.*' : $item['route']; @endphp
                                    <a href="{{ route($item['route']) }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($itemActive) ? 'bg-brand-600 text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="p-3 border-t border-stone-800 space-y-1">
                    <a href="{{ route('admin.settings') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-brand-600 text-white' : '' }}">
                        <i data-lucide="settings" class="w-5 h-5"></i> Settings
                    </a>
                    <a href="{{ route('admin.password') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium {{ request()->routeIs('admin.password') ? 'bg-brand-600 text-white' : '' }}">
                        <i data-lucide="key-round" class="w-5 h-5"></i> Change Password
                    </a>
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium">
                        <i data-lucide="store" class="w-5 h-5"></i> View Store
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-red-600/10 hover:text-red-400 text-sm font-medium text-stone-400">
                            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                        </button>
                    </form>
                </div>
            </aside>

            @if (isset($mobileNav) && $mobileNav)
                {{ $mobileNav }}
            @else
                <div class="lg:hidden fixed inset-0 z-40" x-show="sidebarOpen" x-cloak @keydown.escape.window="sidebarOpen = false" x-transition.opacity>
                    <div class="absolute inset-0 bg-black/50" @click="sidebarOpen = false"></div>
                    <div class="absolute inset-y-0 left-0 w-64 bg-stone-900 text-stone-300 flex flex-col shadow-2xl" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                        <div class="flex items-center justify-between px-5 h-16 border-b border-stone-800">
                            <span class="flex items-center gap-2">
                    <x-logo class="w-9 h-9 rounded-xl" />
                                <div>
                                    <p class="font-bold text-white leading-tight">{{ config('app.name') }}</p>
                                    <p class="text-[11px] text-stone-400">Admin Panel</p>
                                </div>
                            </span>
                            <button type="button" @click="sidebarOpen = false" class="flex items-center justify-center w-9 h-9 rounded-lg text-stone-400 hover:text-white hover:bg-stone-800" aria-label="Close menu">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-4 text-sm font-medium">
                            <div class="space-y-1">
                                @foreach ($topItems as $item)
                                    @php $itemActive = str_ends_with($item['route'], '.index') ? \Illuminate\Support\Str::beforeLast($item['route'], '.').'.*' : $item['route']; @endphp
                                    <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($itemActive) ? 'bg-brand-600 text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                            @foreach ($groups as $group)
                                @php
                                    $isGroupActive = collect($group['items'])->contains(fn($i) => request()->routeIs($i['match']));
                                    $groupPaths = collect($group['items'])->map(fn($i) => parse_url(route($i['route']), PHP_URL_PATH))->values()->all();
                                @endphp
                                <div x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }" data-prefixes='@json($groupPaths)' @@livewire:navigated.window="open = JSON.parse($el.dataset.prefixes).some(p => window.location.pathname.startsWith(p)) || open">
                                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-3 py-1.5 text-[11px] uppercase tracking-wide text-stone-400 hover:text-stone-300">
                                        <span>{{ $group['label'] }}</span>
                                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="open ? '' : '-rotate-90'"></i>
                                    </button>
                                    <div x-show="open" class="mt-1 space-y-1">
                                        @foreach ($group['items'] as $item)
                                            @php $itemActive = str_ends_with($item['route'], '.index') ? \Illuminate\Support\Str::beforeLast($item['route'], '.').'.*' : $item['route']; @endphp
                                            <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($itemActive) ? 'bg-brand-600 text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                                                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                                                {{ $item['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </nav>

                        <div class="p-3 border-t border-stone-800 space-y-1">
                            <a href="{{ route('admin.settings') }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-brand-600 text-white' : '' }}">
                                <i data-lucide="settings" class="w-5 h-5"></i> Settings
                            </a>
                            <a href="{{ route('admin.password') }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium {{ request()->routeIs('admin.password') ? 'bg-brand-600 text-white' : '' }}">
                                <i data-lucide="key-round" class="w-5 h-5"></i> Change Password
                            </a>
                            <a href="{{ route('home') }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium">
                                <i data-lucide="store" class="w-5 h-5"></i> View Store
                            </a>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-red-600/10 hover:text-red-400 text-sm font-medium text-stone-400">
                                    <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
                <header class="bg-white border-b border-stone-200 h-16 flex items-center justify-between gap-3 px-4 sm:px-6 sticky top-0 z-30">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" @click="sidebarOpen = true" class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg hover:bg-stone-100 text-stone-500" aria-label="Open menu">
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                        <h1 class="font-semibold text-stone-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <livewire:admin.notification-bell />
                        <livewire:admin.low-stock-bell />
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-semibold text-sm">{{ auth('admin')->user()->initials() }}</span>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium leading-tight">{{ auth('admin')->user()->name }}</p>
                                <p class="text-xs text-stone-400">Administrator</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}" class="flex items-center">
                            @csrf
                            <button type="submit" class="flex items-center justify-center w-9 h-9 rounded-lg hover:bg-red-50 text-stone-400 hover:text-red-600" aria-label="Logout" title="Logout">
                                <i data-lucide="log-out" class="w-5 h-5"></i>
                            </button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-confirm-modal />

        <x-toast :initial-message="session('success')" initial-type="success" />

        @livewireScripts
    </body>
</html>
