<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Login' }} - {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#F7F8F5] text-stone-900 antialiased min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="flex flex-col items-center mb-6">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white shadow-lg mb-3"><i data-lucide="leaf" class="w-7 h-7"></i></span>
                <h1 class="text-2xl font-bold text-stone-900">{{ config('app.name') }}</h1>
                <p class="text-sm text-stone-500">Fresh fruits & vegetables, delivered.</p>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="text-center text-xs text-stone-400 mt-6">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        @livewireScripts
    </body>
</html>
