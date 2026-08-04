<div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="user" class="w-7 h-7"></i></span>
            <div>
                <h1 class="text-2xl font-bold text-stone-900">My Profile</h1>
                <p class="text-sm text-stone-500">Manage your account details and saved addresses.</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <section class="bg-white rounded-2xl border border-stone-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-semibold text-stone-900">Account Details</h2>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600"><i data-lucide="shield-check" class="w-4 h-4"></i> Verified</span>
                    </div>

                    <form wire:submit="saveProfile" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Full name</label>
                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email (optional)</label>
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                placeholder="you@example.com"
                                class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-stone-700 mb-1">Phone</label>
                            <div class="relative">
                                <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                                <input
                                    id="phone"
                                    type="text"
                                    value="{{ auth()->user()->phone }}"
                                    disabled
                                    class="w-full rounded-xl border-stone-200 bg-stone-50 text-stone-500 pl-9 pr-3 py-2.5 text-sm"
                                />
                            </div>
                            <p class="mt-1 text-xs text-stone-400">Your phone number is your login and cannot be changed.</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 transition">
                                <i data-lucide="check" class="w-4 h-4"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </section>

                <section class="bg-white rounded-2xl border border-stone-200 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-semibold text-stone-900">Saved Addresses</h2>
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
                                                    wire:click="deleteAddress({{ $address->id }})"
                                                    wire:confirm="Delete this address?"
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
                                            <button type="button" wire:click="setDefaultAddress({{ $address->id }})" class="mt-3 text-xs font-semibold text-brand-600 hover:text-brand-700">Set as default</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <form wire:submit="saveAddress" class="space-y-4">
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="label" class="block text-sm font-medium text-stone-700 mb-1">Label</label>
                                    <select id="label" wire:model="label" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                        <option value="Home">Home</option>
                                        <option value="Work">Work</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="receiverName" class="block text-sm font-medium text-stone-700 mb-1">Receiver name</label>
                                    <input id="receiverName" type="text" wire:model="receiverName" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('receiverName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="receiverPhone" class="block text-sm font-medium text-stone-700 mb-1">Receiver phone</label>
                                    <input id="receiverPhone" type="tel" wire:model="receiverPhone" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('receiverPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="addressLine" class="block text-sm font-medium text-stone-700 mb-1">Address</label>
                                <input id="addressLine" type="text" wire:model="addressLine" placeholder="Flat / house no, street, area" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                @error('addressLine') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="landmark" class="block text-sm font-medium text-stone-700 mb-1">Landmark (optional)</label>
                                    <input id="landmark" type="text" wire:model="landmark" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('landmark') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="city" class="block text-sm font-medium text-stone-700 mb-1">City</label>
                                    <input id="city" type="text" wire:model="city" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="state" class="block text-sm font-medium text-stone-700 mb-1">State</label>
                                    <input id="state" type="text" wire:model="state" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="pincode" class="block text-sm font-medium text-stone-700 mb-1">PIN code</label>
                                    <input id="pincode" type="text" wire:model="pincode" inputmode="numeric" maxlength="6" class="w-full rounded-xl border-stone-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    @error('pincode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-stone-700">
                                <input type="checkbox" wire:model="isDefault" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500" />
                                Set as default address
                            </label>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" wire:click="cancelAddressForm" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100 transition">Cancel</button>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition">
                                    <i data-lucide="check" class="w-4 h-4"></i> {{ $this->editingAddressId ? 'Update Address' : 'Save Address' }}
                                </button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200 p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-11 h-11 rounded-full bg-brand-100 text-brand-700 font-bold">{{ auth()->user()->initials() }}</span>
                        <div>
                            <p class="font-semibold text-stone-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-stone-400">Member since {{ auth()->user()->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('orders.index') }}" wire:navigate class="mt-5 flex items-center justify-between rounded-xl border border-stone-200 hover:border-brand-300 px-4 py-3 text-sm font-medium text-stone-700 transition">
                        <span class="inline-flex items-center gap-2"><i data-lucide="package" class="w-4 h-4 text-stone-400"></i> My Orders</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-stone-400"></i>
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
