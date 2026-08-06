@props([
    'class' => 'w-9 h-9 rounded-xl',
    'icon' => 'w-5 h-5',
    'alt' => config('app.name').' logo',
    'src' => null,
])

@php($url = $src ?? \App\Models\Setting::logoUrl())

@if ($url)
    <img src="{{ $url }}" alt="{{ $alt }}" class="{{ $class }} bg-white object-cover">
@else
    <span class="{{ $class }} flex items-center justify-center bg-brand-600 text-white">
        <i data-lucide="leaf" class="{{ $icon }}"></i>
    </span>
@endif
