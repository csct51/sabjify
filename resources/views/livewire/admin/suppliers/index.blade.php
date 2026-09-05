<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-semibold text-stone-900">Suppliers</h1>
            <p class="text-sm text-stone-500 mt-0.5">{{ $suppliers->total() }} suppliers</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 text-sm font-semibold transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Supplier
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100">
            <div class="relative max-w-sm">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, contact or address..." class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Contact</th>
                        <th class="px-4 py-3 text-left font-medium">Address</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($suppliers as $supplier)
                        <tr wire:key="supplier-{{ $supplier->id }}" class="hover:bg-stone-50">
                            <td class="px-4 py-3 text-stone-400">{{ $suppliers->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $supplier->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ $supplier->contact }}</td>
                            <td class="px-4 py-3 text-stone-500 max-w-xs truncate">{{ $supplier->address }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">View</a>
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                                    <button type="button" data-confirm-message="Delete supplier '{{ $supplier->name }}'?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $supplier->id }}) })" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="users" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No suppliers yet</p>
                                <p class="text-xs text-stone-500 mt-1">Add a supplier to start tracking purchases.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="p-4 border-t border-stone-100">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>
