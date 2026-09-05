<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.purchases.index') }}" wire:navigate class="hover:text-brand-600">← Purchases</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">{{ $purchase->purchase_number }}</h2>
                <p class="text-sm text-stone-500 mt-0.5">Purchase details · stock added in base units</p>
            </div>
            <a href="{{ route('admin.purchases.edit', $purchase) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-200 hover:bg-stone-50 px-4 py-2 text-sm font-semibold">Edit</a>
        </div>

        <dl class="grid sm:grid-cols-4 gap-4 text-sm mb-6">
            <div>
                <dt class="text-stone-400">Supplier</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $purchase->supplier_name ?? $purchase->supplier?->name ?? 'Cash' }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Date</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $purchase->purchase_date?->format('d M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Total</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ \Illuminate\Support\Number::currency($purchase->total_amount, 'INR') }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Remark</dt>
                <dd class="text-stone-700 mt-0.5">{{ $purchase->remark ?? '—' }}</dd>
            </div>
        </dl>

        <h3 class="text-sm font-semibold text-stone-900 mb-3">Items</h3>
        <div class="overflow-x-auto rounded-xl border border-stone-200">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Product</th>
                        <th class="px-4 py-3 text-right font-medium">Rate</th>
                        <th class="px-4 py-3 text-right font-medium">Qty</th>
                        <th class="px-4 py-3 text-center font-medium">Unit</th>
                        <th class="px-4 py-3 text-right font-medium">Line Total</th>
                        <th class="px-4 py-3 text-right font-medium">Base Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($purchase->items as $item)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 font-medium text-stone-900">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-stone-700">₹ {{ $item->rate }}</td>
                            <td class="px-4 py-3 text-right text-stone-700">{{ rtrim(rtrim(number_format((float) $item->qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $item->unit }}</td>
                            <td class="px-4 py-3 text-right font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($item->line_total, 'INR') }}</td>
                            <td class="px-4 py-3 text-right text-stone-500">{{ rtrim(rtrim(number_format((float) $item->base_qty, 3, '.', ''), '0'), '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
