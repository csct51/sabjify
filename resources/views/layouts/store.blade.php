<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }} - Fresh Fruits & Vegetables</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#F7F8F5] text-stone-900 antialiased flex flex-col min-h-screen pb-16">
        <livewire:store-header />

        <main class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.footer')

        <livewire:store-bottom-nav />

        <x-confirm-modal />

        <livewire:product-unit-picker />

        <x-toast :initial-message="session('success')" initial-type="success" />

        @livewireScripts
    </body>
</html>
