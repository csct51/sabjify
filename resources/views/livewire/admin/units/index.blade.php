<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $units->total() }} units</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Units</h2>
            <p class="text-sm text-stone-500">Units available when adding or editing products.</p>
        </div>
        <a href="{{ route('admin.units.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Unit
        </a>
    </div>

    @error('remove')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100">
            <div class="relative max-w-sm">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search units..." class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="units-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Unit</th>
                        <th class="px-4 py-3 font-medium">Base Unit</th>
                        <th class="px-4 py-3 font-medium">Factor</th>
                        <th class="px-4 py-3 font-medium">Sort Order</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}" class="hover:bg-stone-50">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.units.show', $unit) }}" wire:navigate class="inline-flex items-center gap-2 font-medium text-stone-900 hover:text-brand-700">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-stone-100 text-stone-500"><i data-lucide="scale" class="w-4 h-4"></i></span>
                                    {{ $unit->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $unit->base_unit ?? '—' }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $unit->to_base_factor }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $unit->sort_order }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $unit->products_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.units.show', $unit) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">View</a>
                                    <a href="{{ route('admin.units.edit', $unit) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                                    <button type="button" data-confirm-message="Delete unit '{{ $unit->name }}'?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $unit->id }}) })" class="rounded-lg border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="scale" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No units found</p>
                                <p class="text-xs text-stone-500 mt-1">Try a different search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($units->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
