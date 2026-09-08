@props(['compact' => false])

@php($cards = \App\Support\InfoCards::all())

<div class="grid gap-4 {{ $compact ? 'grid-cols-3 text-center' : 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6' }}">
    @foreach ($cards as $card)
        @if ($compact)
            <div>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i></span>
                <p class="text-xs font-medium text-stone-700 mt-2">{{ $card['title'] }}</p>
                @if ($card['subtitle'] !== '')
                    <p class="text-[11px] text-stone-400">{{ $card['subtitle'] }}</p>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                <span class="inline-flex items-center justify-center w-11 h-11 mx-auto rounded-xl bg-brand-50 text-brand-600"><i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i></span>
                <p class="mt-2 text-sm font-semibold text-stone-800">{{ $card['title'] }}</p>
                @if ($card['subtitle'] !== '')
                    <p class="text-xs text-stone-400">{{ $card['subtitle'] }}</p>
                @endif
            </div>
        @endif
    @endforeach
</div>
