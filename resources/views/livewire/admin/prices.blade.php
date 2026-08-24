<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $this->units->count() }} units</p>
            <h2 class="text-lg font-semibold text-stone-900">Update Prices</h2>
        </div>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition disabled:opacity-70">
            <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
            <span wire:loading.remove wire:target="save">
                <i data-lucide="check" class="w-4 h-4"></i>
            </span>
            Save Changes
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap items-center gap-3">
            <select wire:model.live="category" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search products..." class="w-56 rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
            </div>
        </div>

        @if ($this->units->isEmpty())
            <div class="text-center py-16">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="indian-rupee" class="w-7 h-7"></i></span>
                <p class="mt-4 font-medium text-stone-700">No units found</p>
                <p class="text-sm text-stone-400 mt-1">Choose a different category.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Unit</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">MRP</th>
                            <th class="px-4 py-3 font-medium text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @php $currentCategoryId = null; $currentProductId = null; @endphp
                        @foreach ($this->units as $unit)
                            @if ($unit->product?->category_id !== $currentCategoryId)
                                @php $currentCategoryId = $unit->product?->category_id; @endphp
                                <tr class="bg-brand-50/40">
                                    <td colspan="5" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-brand-700">{{ $unit->product?->category?->name }}</td>
                                </tr>
                            @endif
                            <tr class="hover:bg-stone-50" wire:key="unit-{{ $unit->id }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                            <img src="{{ $unit->product?->displayImageUrl() }}" alt="" class="w-full h-full object-cover">
                                        </span>
                                        <span class="font-medium text-stone-900">{{ $unit->product?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-stone-500">{{ $unit->unit }}</td>
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">₹</span>
                                        <input wire:model="prices.{{ $unit->id }}" type="number" min="1" inputmode="numeric" class="w-28 rounded-lg border border-stone-300 pl-7 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">₹</span>
                                        <input wire:model="mrps.{{ $unit->id }}" type="number" min="1" inputmode="numeric" placeholder="—" class="w-28 rounded-lg border border-stone-300 pl-7 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="toggleUnitStock({{ $unit->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleUnitStock({{ $unit->id }})"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition disabled:opacity-50 {{ $unit->in_stock ? 'bg-brand-600' : 'bg-stone-300' }}"
                                        role="switch"
                                        aria-checked="{{ $unit->in_stock ? 'true' : 'false' }}"
                                        aria-label="Toggle stock for {{ $unit->product?->name }} ({{ $unit->unit }})"
                                    >
                                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition {{ $unit->in_stock ? 'translate-x-[22px]' : 'translate-x-0.5' }}"></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
