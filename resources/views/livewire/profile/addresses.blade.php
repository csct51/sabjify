<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-700"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Profile</a>
            <div class="flex items-center gap-4 mt-4">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="map-pin" class="w-7 h-7"></i></span>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Saved Addresses</h1>
                    <p class="text-sm text-stone-500">Manage your delivery addresses.</p>
                </div>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-stone-900">My Addresses</h2>
                @if ($this->addressMode === 'list')
                    <button type="button" wire:click="openAddressForm" class="inline-flex items-center gap-1.5 rounded-xl border border-brand-600 text-brand-600 hover:bg-brand-50 font-semibold px-4 py-2 text-sm transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Address
                    </button>
                @endif
            </div>

            @if ($this->addressMode === 'list')
                @if ($this->addresses->isEmpty())
                    <div class="text-center py-10">
                        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="map-pin" class="w-7 h-7"></i></span>
                        <p class="mt-3 font-medium text-stone-700">No saved addresses yet</p>
                        <p class="text-sm text-stone-500 mt-1">Add your delivery address so checkout is faster.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($this->addresses as $address)
                            <div class="rounded-xl border border-stone-200 p-4 {{ $address->is_default ? 'border-brand-300 bg-brand-50/50' : '' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-stone-900">{{ $address->label }}</span>
                                        @if ($address->is_default)
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-700"><i data-lucide="check" class="w-3.5 h-3.5"></i> Default</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" wire:click="editAddress({{ $address->id }})" class="p-2 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-100" aria-label="Edit address">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <button
                                            type="button"
                                            @click="$dispatch('confirm-modal', { message: 'Delete this address?', action: () => $wire.deleteAddress({{ $address->id }}) })"
                                            class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50"
                                            aria-label="Delete address"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-stone-600">{{ $address->receiver_name }} · {{ $address->receiver_phone }}</p>
                                <p class="text-sm text-stone-500">{{ $address->address_line }}{{ $address->landmark ? ', ' . $address->landmark : '' }}, {{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                @if (! $address->is_default)
                                    <button type="button" wire:click="setDefaultAddress({{ $address->id }})" wire:loading.attr="disabled" wire:target="setDefaultAddress({{ $address->id }})" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 disabled:opacity-50">
                                        <x-loading-spinner wire:loading wire:target="setDefaultAddress({{ $address->id }})" class="w-3 h-3" />
                                        <span wire:loading.remove wire:target="setDefaultAddress({{ $address->id }})">Set as default</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <form wire:submit="saveAddress" class="space-y-4">
                    <div class="grid sm:grid-cols-3 gap-4">
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
                        />
                        @error('latitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('longitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-stone-700">
                        <input type="checkbox" wire:model="isDefault" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500" />
                        Set as default address
                    </label>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="cancelAddressForm" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100 transition">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveAddress" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70">
                            <x-loading-spinner wire:loading wire:target="saveAddress" class="w-4 h-4" />
                            <span wire:loading.remove.inline-flex wire:target="saveAddress" class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i> {{ $this->editingAddressId ? 'Update Address' : 'Save Address' }}</span>
                            <span wire:loading wire:target="saveAddress">Saving...</span>
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</div>