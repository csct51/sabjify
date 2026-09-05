<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-semibold text-stone-900">Purchases</h1>
            <p class="text-sm text-stone-500 mt-0.5">Track purchases by product, unit, rate and quantity. Saving a purchase adds to stock.</p>
        </div>
        <a href="{{ route('admin.purchases.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-4 py-2.5 text-sm font-semibold transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Purchase
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-semibold text-stone-900">Recent Purchases</h2>
            <span class="text-xs text-stone-400">{{ $purchases->count() }} purchases</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Purchase No</th>
                        <th class="px-4 py-3 text-left font-medium">Supplier</th>
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-right font-medium">Products</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 text-stone-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-stone-900">
                                <a href="{{ route('admin.purchases.show', $purchase) }}" wire:navigate class="hover:text-brand-700">{{ $purchase->purchase_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ $purchase->supplier_name ?? $purchase->supplier?->name ?? 'Cash' }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $purchase->purchase_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-stone-600">{{ $purchase->items_count ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-stone-900">{{ isset($purchase->total) ? \Illuminate\Support\Number::currency($purchase->total, 'INR') : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.purchases.edit', $purchase) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                                    <button type="button" data-confirm-message="Delete purchase {{ $purchase->purchase_number }}? This will revert stock." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $purchase->id }}) })" class="rounded-lg border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 text-xs font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="package" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No purchases yet</p>
                                <p class="text-xs text-stone-500 mt-1">Create a purchase to see it here. Purchases will be saved once functionality is enabled.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
