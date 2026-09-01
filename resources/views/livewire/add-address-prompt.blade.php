<div>
    @if ($this->show)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] overflow-y-auto p-5">
                @if ($view === 'select')
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-600 text-white shrink-0"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-base font-bold text-stone-900">Select a delivery address</h2>
                            <p class="text-xs text-stone-500">Choose where we should deliver your order.</p>
                        </div>
                        @if ($dismissable)
                            <button type="button" wire:click="dismiss" aria-label="Close" class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>

                    @if ($this->addresses->isEmpty())
                        <div class="mt-5 py-6 text-center">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="package" class="w-6 h-6"></i></span>
                            <p class="mt-3 font-medium text-stone-700">No saved addresses yet</p>
                            <p class="mt-1 text-sm text-stone-500">Add your delivery address to get started.</p>
                        </div>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach ($this->addresses as $address)
                                <button
                                    type="button"
                                    wire:click="selectAddress({{ $address->id }})"
                                    class="w-full text-left rounded-xl border p-4 transition {{ $selectedAddressId === $address->id ? 'border-brand-500 bg-brand-50' : 'border-stone-200 hover:border-stone-300' }}"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-2 min-w-0">
                                            <span class="shrink-0 rounded-md bg-white border border-stone-200 px-2 py-0.5 text-xs font-medium text-stone-600">{{ $address->label }}</span>
                                            <span class="truncate text-sm font-medium text-stone-900">{{ $address->receiver_name }}</span>
                                        </span>
                                        <span class="shrink-0 w-4 h-4 rounded-full border-2 {{ $selectedAddressId === $address->id ? 'border-brand-600 bg-brand-600' : 'border-stone-300' }}"></span>
                                    </div>
                                    <p class="mt-1.5 text-sm text-stone-500">{{ $address->receiver_phone }}</p>
                                    <p class="mt-1 text-sm text-stone-600">{{ $address->address_line }}{{ $address->landmark ? ', '.$address->landmark : '' }}</p>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @error('selection') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-4 flex flex-col gap-2">
                        <button type="button" wire:click="openAddForm" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-brand-600 text-brand-600 hover:bg-brand-50 font-semibold px-5 py-2.5 text-sm transition">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add New Address
                        </button>
                        @if ($this->addresses->isNotEmpty())
                            <button
                                type="button"
                                wire:click="confirmSelection"
                                wire:loading.attr="disabled"
                                wire:target="confirmSelection"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70 disabled:cursor-not-allowed {{ $selectedAddressId === null ? 'opacity-50' : '' }}"
                            >
                                Confirm Address
                            </button>
                        @endif
                    </div>
                @elseif($view === 'guest')
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-600 text-white shrink-0"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-base font-bold text-stone-900">Set delivery location</h2>
                            <p class="text-xs text-stone-500">Tap inside the green circle to set your delivery location. Our service is currently available only within the highlighted areas.</p>
                        </div>
                        @if ($dismissable)
                            <button type="button" wire:click="dismiss" aria-label="Close" class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>

                    <form wire:submit="saveGuestLocation" class="mt-4 space-y-3">
                        <div>
                            <label for="guest-addressLine" class="block text-sm font-medium text-stone-700 mb-1">Address <span class="text-red-500">*</span></label>
                            <input id="guest-addressLine" type="text" wire:model="addressLine" placeholder="Flat / house no, street, area" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                            @error('addressLine') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="guest-landmark" class="block text-sm font-medium text-stone-700 mb-1">Landmark (optional)</label>
                            <input id="guest-landmark" type="text" wire:model="landmark" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                            @error('landmark') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Delivery Location</label>
                            <x-location-map
                                :lat="$latitude"
                                :lng="$longitude"
                                lat-prop="latitude"
                                lng-prop="longitude"
                                :autofill="true"
                                geolocate
                                height="h-48"
                            />
                            @error('latitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('longitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('delivery') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @if ($latitude !== null && $longitude !== null)
                                <div class="mt-2">
                                    @if ($this->deliveryLocations()->isEmpty() || $this->checkDeliverable((float) $latitude, (float) $longitude))
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 bg-green-50 text-green-700 px-3 py-1 text-xs font-medium">Delivery available</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700 px-3 py-1 text-xs font-medium">Coming soon — outside delivery area</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveGuestLocation" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70">
                                <x-loading-spinner wire:loading wire:target="saveGuestLocation" class="w-4 h-4" />
                                <span wire:loading.remove.inline-flex wire:target="saveGuestLocation" class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i> Confirm location</span>
                                <span wire:loading wire:target="saveGuestLocation">Saving...</span>
                            </button>
                            <a href="{{ route('login') }}" wire:navigate class="text-center text-sm font-medium text-brand-600 hover:text-brand-700">Login to save address</a>
                            @if ($dismissable)
                                <button type="button" wire:click="dismiss" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold px-5 py-2.5 text-sm transition">
                                    <i data-lucide="x" class="w-4 h-4"></i> Close
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-2xl bg-brand-600 text-white shrink-0"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                        <div>
                            <h2 class="text-base font-bold text-stone-900">Add your delivery address</h2>
                            <p class="text-xs text-stone-500">We need an address before you can place an order.</p>
                        </div>
                    </div>

                    <form wire:submit="saveAddress" class="mt-4 space-y-3">
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div>
                                <label for="label" class="block text-sm font-medium text-stone-700 mb-1">Label <span class="text-red-500">*</span></label>
                                <select id="label" wire:model="label" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    <option value="Home">Home</option>
                                    <option value="Work">Work</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="receiverName" class="block text-sm font-medium text-stone-700 mb-1">Receiver name <span class="text-red-500">*</span></label>
                                <input id="receiverName" type="text" wire:model="receiverName" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                                @error('receiverName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="receiverPhone" class="block text-sm font-medium text-stone-700 mb-1">Receiver phone <span class="text-red-500">*</span></label>
                                <input id="receiverPhone" type="tel" wire:model="receiverPhone" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                                @error('receiverPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="addressLine" class="block text-sm font-medium text-stone-700 mb-1">Address <span class="text-red-500">*</span></label>
                            <input id="addressLine" type="text" wire:model="addressLine" placeholder="Flat / house no, street, area" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                            @error('addressLine') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="landmark" class="block text-sm font-medium text-stone-700 mb-1">Landmark (optional)</label>
                            <input id="landmark" type="text" wire:model="landmark" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                            @error('landmark') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Delivery Location</label>
                            <x-location-map
                                :lat="$latitude"
                                :lng="$longitude"
                                lat-prop="latitude"
                                lng-prop="longitude"
                                :autofill="true"
                                geolocate
                                height="h-48"
                            />
                            @error('latitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('longitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @error('delivery') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveAddress" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70">
                                <x-loading-spinner wire:loading wire:target="saveAddress" class="w-4 h-4" />
                                <span wire:loading.remove.inline-flex wire:target="saveAddress" class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i> Save Address</span>
                                <span wire:loading wire:target="saveAddress">Saving...</span>
                            </button>
                            @if ($this->addresses->isNotEmpty())
                                <button type="button" wire:click="backToSelect" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-stone-300 text-stone-600 hover:bg-stone-50 font-semibold px-5 py-2.5 text-sm transition">
                                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to addresses
                                </button>
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>