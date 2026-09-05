<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-semibold text-stone-900">Wastage</h1>
            <p class="text-sm text-stone-500 mt-0.5">Record wastage and track stock reduction</p>
        </div>
        <a href="{{ route('admin.wastages.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 text-sm font-semibold transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Wastage
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by wastage no, reason or product..." class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Wastage No</th>
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-left font-medium">Reason</th>
                        <th class="px-4 py-3 text-right font-medium">Products</th>
                        <th class="px-4 py-3 text-right font-medium">Qty</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($wastages as $wastage)
                        <tr class="hover:bg-stone-50" wire:key="wastage-{{ $wastage->id }}">
                            <td class="px-4 py-3 font-medium text-stone-900">
                                <a href="{{ route('admin.wastages.show', $wastage) }}" wire:navigate class="hover:text-brand-700">{{ $wastage->wastage_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $wastage->wastage_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-stone-100 text-stone-600 border border-stone-200">{{ $wastage->reason }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-stone-600">{{ $wastage->items_count }}</td>
                            <td class="px-4 py-3 text-right font-medium text-stone-900">{{ $wastage->total_qty }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.wastages.edit', $wastage) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                                    <button type="button" data-confirm-message="Delete wastage {{ $wastage->wastage_number }}? This will restore stock." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $wastage->id }}) })" class="rounded-lg border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="trash-2" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No wastage yet</p>
                                <p class="text-xs text-stone-500 mt-1">Record wastage to see it here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($wastages->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $wastages->links() }}
            </div>
        @endif
    </div>
</div>
