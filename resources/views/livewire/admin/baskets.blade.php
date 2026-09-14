<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $baskets->count() }} baskets</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Baskets</h2>
        </div>
        <a href="{{ route('admin.baskets.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Basket
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="baskets-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Basket</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-right">Price</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($baskets as $basket)
                        <tr class="hover:bg-stone-50" wire:key="basket-{{ $basket->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                        <img src="{{ str_replace('/storage/', '/public/storage/', $basket->displayImageUrl()) }}" alt="{{ $basket->name }}" class="w-full h-full object-cover">
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.baskets.edit', $basket) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $basket->name }}</a>
                                        <p class="text-xs text-stone-400">Sort: {{ $basket->sort_order }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-brand-50 text-brand-700 text-xs font-semibold px-2.5 py-0.5">{{ $basket->typeLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $basket->products_count }}</td>
                            <td class="px-4 py-3 text-right font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($basket->price, 'INR') }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $basket->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $basket->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $basket->is_active ? 'text-green-600' : 'text-stone-400' }} disabled:opacity-50">
                                    <x-loading-spinner wire:loading wire:target="toggleActive({{ $basket->id }})" class="w-3 h-3" />
                                    <span wire:loading.remove wire:target="toggleActive({{ $basket->id }})" class="w-2 h-2 rounded-full {{ $basket->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    <span wire:loading.remove wire:target="toggleActive({{ $basket->id }})">{{ $basket->is_active ? 'Active' : 'Hidden' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.baskets.edit', $basket) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">Edit</a>
                                    <button type="button" data-confirm-message="Delete {{ $basket->name }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $basket->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
