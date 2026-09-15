@props([
    'class' => 'w-9 h-9 rounded-xl',
    'icon' => 'w-5 h-5',
    'alt' => config('app.name').' logo',
    'src' => null,
    'objectFit' => 'cover',
    'boxed' => true,
])

@php($url = $src ?? \App\Models\Setting::logoUrl())
@php($fit = $objectFit === 'contain' ? 'contain' : 'cover')

@if ($url)
<img src="{{ $url }}" alt="{{ $alt }}" class="{{ $class }} {{ $boxed ? 'bg-white' : '' }} {{ $fit === 'contain' ? 'object-contain' : 'object-cover' }}">
@else
    <span class="{{ $class }} flex items-center justify-center bg-brand-600 text-white">
        <i data-lucide="leaf" class="{{ $icon }}"></i>
    </span>
@endif
