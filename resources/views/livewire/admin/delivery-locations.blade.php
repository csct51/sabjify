<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $locations->count() }} locations</p>
            <h2 class="text-lg font-semibold text-stone-900">Delivery Locations</h2>
            <p class="text-sm text-stone-500 mt-1">Define the areas your store delivers to. Customers can only checkout to addresses inside an active location.</p>
        </div>
        <a href="{{ route('admin.delivery-locations.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Location
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="delivery-locations-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Location</th>
                        <th class="px-4 py-3 font-medium">Coordinates</th>
                        <th class="px-4 py-3 font-medium text-center">Radius</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($locations as $location)
                        <tr class="hover:bg-stone-50" wire:key="location-{{ $location->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.delivery-locations.edit', $location) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $location->name }}</a>
                                <p class="text-xs text-stone-400">Sort: {{ $location->sort_order }}</p>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $location->latitude }}, {{ $location->longitude }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $location->radius_km }} km</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $location->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $location->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $location->is_active ? 'text-green-600' : 'text-stone-400' }} disabled:opacity-50">
                                    <x-loading-spinner wire:loading wire:target="toggleActive({{ $location->id }})" class="w-3 h-3" />
                                    <span wire:loading.remove wire:target="toggleActive({{ $location->id }})" class="w-2 h-2 rounded-full {{ $location->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    <span wire:loading.remove wire:target="toggleActive({{ $location->id }})">{{ $location->is_active ? 'Active' : 'Disabled' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.delivery-locations.edit', $location) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">Edit</a>
                                    <button type="button" data-confirm-message="Delete {{ $location->name }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $location->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>