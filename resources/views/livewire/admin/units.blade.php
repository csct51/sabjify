<div>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-stone-400 mb-1">{{ count($this->units) }} units</p>
                <h2 class="text-lg font-semibold text-stone-900">Units</h2>
                <p class="text-sm text-stone-400 mt-0.5">Units available when adding or editing products.</p>
            </div>
        </div>

        @error('remove')
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="p-4 border-b border-stone-100">
                <form wire:submit="addUnit" class="flex items-center gap-2">
                    <input wire:model="newUnit" type="text" placeholder="e.g. 750 ml" class="flex-1 rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 text-sm font-semibold transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Unit
                    </button>
                </form>
                @error('newUnit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="overflow-x-auto">
            <table id="units-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Unit</th>
                        <th class="px-4 py-3 font-medium">Sort Order</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($this->units as $unit)
                        <tr class="hover:bg-stone-50" wire:key="unit-{{ $unit->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 text-stone-400">
                                            <i data-lucide="scale" class="w-4 h-4"></i>
                                        </span>
                                        <p class="font-medium text-stone-900">{{ $unit->name }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-stone-500">{{ $unit->sort_order }}</td>
                                <td class="px-4 py-3 text-center text-stone-600">{{ $unit->products_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" data-confirm-message="Delete {{ $unit->name }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.removeUnit({{ $unit->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
