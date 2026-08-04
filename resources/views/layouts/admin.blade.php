<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name') }} Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-stone-100 text-stone-900 antialiased min-h-screen">
        <div class="flex min-h-screen">
            <aside class="hidden lg:flex flex-col w-64 bg-stone-900 text-stone-300 shrink-0 fixed top-0 bottom-0 left-0">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 px-5 h-16 border-b border-stone-800">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 text-white"><i data-lucide="leaf" class="w-5 h-5"></i></span>
                    <div>
                        <p class="font-bold text-white leading-tight">{{ config('app.name') }}</p>
                        <p class="text-[11px] text-stone-400">Admin Panel</p>
                    </div>
                </a>

                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm font-medium">
                    @php
                        $items = [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                            ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => 'package'],
                            ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => 'shopping-basket'],
                            ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => 'folder'],
                            ['route' => 'admin.customers.index', 'label' => 'Customers', 'icon' => 'users'],
                            ['route' => 'admin.settings', 'label' => 'Settings', 'icon' => 'settings'],
                        ];
                    @endphp
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs($item['route'].'*') ? 'bg-brand-600 text-white' : 'hover:bg-stone-800 hover:text-white' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="p-3 border-t border-stone-800">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-sm font-medium">
                        <i data-lucide="store" class="w-5 h-5"></i> View Store
                    </a>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
                <header class="bg-white border-b border-stone-200 h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                    <h1 class="font-semibold text-stone-900">{{ $title ?? 'Dashboard' }}</h1>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="lg:hidden text-sm font-medium text-brand-600">Dashboard</a>
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

        @livewireScripts
    </body>
</html>
