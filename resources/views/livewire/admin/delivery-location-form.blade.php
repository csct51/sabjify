<div>
    <div>
        <nav class="text-sm text-stone-400 mb-4">
            <a href="{{ route('admin.delivery-locations.index') }}" wire:navigate class="hover:text-brand-600">← Delivery Locations</a>
        </nav>

        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-6">{{ $location ? 'Edit Delivery Location' : 'Add Delivery Location' }}</h2>

            <form wire:submit="save" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1">Location Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" placeholder="e.g. Mumbai Central" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Sort Order</label>
                        <input wire:model="sort_order" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Map Center & Radius <span class="text-red-500">*</span></label>
                    <x-location-map
                        :lat="$latitude"
                        :lng="$longitude"
                        :radius-km="$radiusKm"
                        lat-prop="latitude"
                        lng-prop="longitude"
                        radius-prop="radiusKm"
                        existing-areas
                        :exclude-area-id="$location?->id"
                        geolocate
                    />
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-xs font-medium text-stone-500 mb-1">Latitude</label>
                            <input id="latitude" type="text" value="{{ $latitude }}" readonly class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3 py-2 text-sm text-stone-700 outline-none cursor-not-allowed">
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-medium text-stone-500 mb-1">Longitude</label>
                            <input id="longitude" type="text" value="{{ $longitude }}" readonly class="w-full rounded-xl border border-stone-300 bg-stone-50 px-3 py-2 text-sm text-stone-700 outline-none cursor-not-allowed">
                        </div>
                    </div>
                    @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('radiusKm')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="checkbox" wire:model="is_active" value="1" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500" @checked($is_active === '1')>
                    Active — allow checkout to addresses inside this location
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.delivery-locations.index') }}" wire:navigate class="rounded-xl px-5 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100 transition">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 text-sm transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                        <span wire:loading.remove.inline-flex wire:target="save">{{ $location ? 'Update Location' : 'Save Location' }}</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>