<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.units.index') }}" wire:navigate class="hover:text-brand-600">← Units</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <h2 class="text-lg font-semibold text-stone-900 mb-6">Edit Unit</h2>
        <form wire:submit="save" class="space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Sort Order <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="sort_order" min="0" step="1" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                    @error('sort_order') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Base Unit <span class="text-red-500">*</span></label>
                    <select wire:model.live="base_unit" @disabled($is_base) class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm bg-white outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 disabled:bg-stone-50">
                        @foreach ($this->baseOptions as $option)
                            <option value="{{ $option->name }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('base_unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-stone-400">To add a base unit, tick “Is base unit” below instead of picking one.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">To Base Factor <span class="text-red-500">*</span></label>
                    <input type="number" wire:model="to_base_factor" min="0.0001" step="0.0001" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
                    @error('to_base_factor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-stone-400">
                        @if ($name)
                            1 {{ $name ?: 'unit' }} = {{ $to_base_factor }} {{ $is_base ? ($name ?: 'base') : $base_unit }}
                        @else
                            1 unit = {{ $to_base_factor }} {{ $is_base ? 'base' : $base_unit }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-stone-200 p-4 bg-stone-50/40">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
                    <input wire:model.live="is_base" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500" />
                    Is base unit
                </label>
                @error('is_base') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if ($is_base)
                    <div class="grid sm:grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Purchase Unit</label>
                            <input type="text" wire:model="purchase_unit" placeholder="e.g. kg (leave blank for same as name)" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white" />
                            @error('purchase_unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-stone-400">Unit admins type quantities in for this base.</p>
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
                                <input wire:model="integer_only" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500" />
                                Whole numbers only (e.g. piece)
                            </label>
                        </div>
                    </div>
                    @error('integer_only') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.units.index') }}" wire:navigate class="rounded-xl border border-stone-200 hover:bg-stone-50 px-5 py-2.5 text-sm font-semibold text-stone-600 transition">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                    <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
