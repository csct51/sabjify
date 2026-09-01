<div>
    @if ($this->show && is_array($guest) && ! empty($guest['address_line']))
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="dismiss"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-600 text-white shrink-0"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base font-bold text-stone-900">Save your recent location?</h2>
                        <p class="text-xs text-stone-500 mt-0.5">We noticed you set a delivery location as a guest. Save it to your account for faster checkout.</p>
                    </div>
                    <button type="button" wire:click="dismiss" aria-label="Close" class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="mt-4 rounded-xl border border-stone-200 p-4 bg-stone-50/50">
                    <p class="text-sm font-medium text-stone-900">{{ $guest['label'] ?? $guest['address_label'] ?? 'Home' }} · {{ $guest['address_line'] }}</p>
                    @if (! empty($guest['landmark']))
                        <p class="text-xs text-stone-500 mt-1">{{ $guest['landmark'] }}</p>
                    @endif
                    @if (! empty($guest['receiver_name']) || ! empty($guest['receiver_phone']))
                        <p class="text-xs text-stone-500 mt-1">{{ $guest['receiver_name'] ?? '' }} @if (! empty($guest['receiver_phone'])) · {{ $guest['receiver_phone'] }} @endif</p>
                    @endif
                    @php
                        $lat = $guest['latitude'] ?? $guest['lat'] ?? null;
                        $lng = $guest['longitude'] ?? $guest['lng'] ?? null;
                    @endphp
                    @if ($lat !== null && $lng !== null)
                        <div class="mt-2">
                            @if (empty($guest['available']) && isset($guest['available']) && $guest['available'] === false)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700 px-3 py-1 text-xs font-medium">Coming soon — outside delivery area</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 bg-green-50 text-green-700 px-3 py-1 text-xs font-medium">Delivery available</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-col gap-2">
                    <button type="button" wire:click="claim" wire:loading.attr="disabled" wire:target="claim" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="claim" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="claim" class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i> Save to my addresses</span>
                        <span wire:loading wire:target="claim">Saving...</span>
                    </button>
                    <button type="button" wire:click="dismiss" class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold px-5 py-2.5 text-sm transition">
                        Not now
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
