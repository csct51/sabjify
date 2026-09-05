<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.units.index') }}" wire:navigate class="hover:text-brand-600">← Units</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">{{ $unit->name }}</h2>
                <p class="text-sm text-stone-500 mt-0.5">Unit details</p>
            </div>
            <a href="{{ route('admin.units.edit', $unit) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-200 hover:bg-stone-50 px-4 py-2 text-sm font-semibold">Edit</a>
        </div>

        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-stone-400">Name</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $unit->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Base Unit</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $unit->base_unit ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">To Base Factor</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $unit->to_base_factor }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Sort Order</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $unit->sort_order }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Products Using This Unit</dt>
                <dd class="font-medium text-stone-900 mt-0.5">{{ $unit->products_count ?? $unit->products()->count() }}</dd>
            </div>
            <div>
                <dt class="text-stone-400">Created</dt>
                <dd class="text-stone-500 mt-0.5">{{ $unit->created_at->format('d M Y, h:i A') }}</dd>
            </div>
        </dl>
    </div>
</div>
