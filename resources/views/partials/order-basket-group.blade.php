@props(['item', 'imageClass' => 'w-12 h-12'])

<div class="py-3">
    <div class="rounded-xl border border-stone-200 overflow-hidden" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full text-left flex items-center gap-4 px-4 py-3 bg-brand-50/40 hover:bg-brand-50 transition" aria-expanded="false" :aria-expanded="open ? 'true' : 'false'">
            <div class="{{ $imageClass }} rounded-xl bg-gradient-to-br from-brand-50 to-lime-100 flex items-center justify-center shrink-0 overflow-hidden">
                <img src="{{ $item->basket->displayImageUrl() }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-stone-900">{{ $item->product_name }}</p>
                <p class="text-xs text-stone-400">{{ $item->quantity }} × {{ \Illuminate\Support\Number::currency($item->price, 'INR') }} @if ($item->unit) / {{ $item->unit }} @endif</p>
            </div>
            <p class="font-semibold text-stone-900 shrink-0">{{ \Illuminate\Support\Number::currency($item->total, 'INR') }}</p>
            <span class="inline-flex items-center justify-center shrink-0 transition-transform" :class="{ 'rotate-180': open }">
                <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400"></i>
            </span>
        </button>
        <div x-show="open" x-collapse class="divide-y divide-stone-100 border-t border-stone-200">
            @foreach ($item->basket->products as $product)
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <div class="w-10 h-10 rounded-lg bg-stone-100 overflow-hidden shrink-0">
                        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-stone-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-stone-400">{{ $product->pivot->unit ?: $product->unit }}</p>
                    </div>
                    @if ($product->pivot->price !== null)
                        <p class="text-sm font-medium text-stone-700">{{ \Illuminate\Support\Number::currency($product->pivot->price, 'INR') }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
