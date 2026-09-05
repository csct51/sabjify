<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.wastages.index') }}" wire:navigate class="hover:text-brand-600">← Wastage</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-lg font-semibold text-stone-900">Edit Wastage</h2>
            <button type="button" data-confirm-message="Delete wastage {{ $wastageNumber }}? This will restore stock." @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete() })" class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Wastage
            </button>
        </div>
        <form wire:submit="save" class="space-y-5">
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Wastage No</label>
                    <input type="text" value="{{ $wastageNumber }}" readonly class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm font-mono text-stone-600 outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="wastageDate" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                    @error('wastageDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Reason <span class="text-red-500">*</span></label>
                    <select wire:model.live="reason" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <option value="Expired">Expired</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Spoiled">Spoiled</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if ($reason === 'Other')
                        <input type="text" wire:model="customReason" placeholder="Enter reason" class="mt-2 w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                        @error('customReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Remark</label>
                <textarea wire:model="remark" rows="2" placeholder="Optional note" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                @error('remark') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <h3 class="text-sm font-semibold text-stone-900 mb-3">Products</h3>

                <div class="rounded-xl border border-stone-200 p-4 bg-stone-50/40 mb-4">
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-3 items-end">
                        <div>
                            <label class="block text-[11px] font-medium text-stone-500 mb-1">Product <span class="text-red-500">*</span></label>
                            <x-admin.purchases.product-search
                                target="formProductId"
                                search="formProductSearch"
                                :productId="$formProductId"
                            />
                            @error('formProductId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-stone-500 mb-1">Qty ({{ $this->formUnit }}) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model.live="formQty" min="0.001" step="0.001" placeholder="{{ \App\Models\Unit::qtyPlaceholderFor($this->formUnit) }}" class="w-full rounded-lg border border-stone-300 px-2 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                            @error('formQty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col justify-end">
                            <span class="block text-[11px] font-medium text-stone-500 mb-1">&nbsp;</span>
                            <button
                                type="button"
                                wire:click="addProduct"
                                wire:loading.attr="disabled"
                                wire:target="addProduct"
                                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 text-sm font-semibold transition disabled:opacity-70"
                            >
                                <x-loading-spinner wire:loading wire:target="addProduct" class="w-4 h-4" />
                                <span wire:loading.remove wire:target="addProduct" class="inline-flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Add Product
                                </span>
                                <span wire:loading wire:target="addProduct">Adding...</span>
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-stone-500">
                        @if ($this->selectedProduct)
                            <span class="font-medium text-stone-700">Available: {{ $this->selectedProduct->displayStock() }}</span>
                            <span class="text-stone-300"> · </span>
                        @endif
                        {{ \App\Models\Unit::qtyHintFor($this->formUnit) }}
                    </p>
                    @if ($formError)
                        <p class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">{{ $formError }}</p>
                    @endif
                </div>

                <div class="space-y-2">
                    @forelse ($rows as $index => $row)
                        @php
                            $product = $row['product_id'] ? \App\Models\Product::with('category')->find($row['product_id']) : null;
                        @endphp
                        <div wire:key="wastage-row-{{ $index }}" class="rounded-xl border border-stone-200 p-3 bg-white">
                            <div class="grid grid-cols-1 lg:grid-cols-[2fr_110px_110px_40px] gap-3 items-center">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-stone-900 truncate">{{ $product?->name ?? '—' }}</p>
                                    @if ($product?->category)
                                        <p class="text-xs text-stone-400 truncate">{{ $product->category->name }} · {{ $row['unit'] }}</p>
                                    @endif
                                </div>
                                <div class="text-sm text-right text-stone-700">{{ $row['qty'] ?: '—' }}</div>
                                <div class="text-sm text-center text-stone-600">{{ $row['unit'] }}</div>
                                <div class="flex justify-end lg:justify-center">
                                    <button type="button" wire:click="removeRow({{ $index }})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 transition" aria-label="Remove">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 text-center py-4">No products added yet. Use the form above to add products.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.wastages.index') }}" wire:navigate class="rounded-xl border border-stone-200 hover:bg-stone-50 px-5 py-2.5 text-sm font-semibold text-stone-600 transition">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                    <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
