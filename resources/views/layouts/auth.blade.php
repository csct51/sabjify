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
                <x-logo class="w-full max-w-md h-48 rounded-3xl shadow-lg" object-fit="contain" icon="w-16 h-16" />
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="text-center text-xs text-stone-400 mt-6">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        @livewireScripts
    </body>
</html>
