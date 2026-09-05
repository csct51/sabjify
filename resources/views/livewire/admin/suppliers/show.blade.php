<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="hover:text-brand-600">← Suppliers</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">{{ $supplier->name }}</h2>
                <p class="text-sm text-stone-500 mt-0.5">Supplier details</p>
            </div>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-200 hover:bg-stone-50 px-4 py-2 text-sm font-semibold">Edit</a>
        </div>

        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-stone-400">Name</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $supplier->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Contact</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $supplier->contact }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Address</dt>
                <dd class="text-stone-700 mt-0.5 whitespace-pre-wrap">{{ $supplier->address }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Created</dt>
                <dd class="text-stone-500 mt-0.5">{{ $supplier->created_at->format('d M Y, h:i A') }}</dd>
            </div>
        </dl>
    </div>
</div>
