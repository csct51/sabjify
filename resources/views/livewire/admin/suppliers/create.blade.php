<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="hover:text-brand-600">← Suppliers</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <h2 class="text-lg font-semibold text-stone-900 mb-6">Add Supplier</h2>
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Supplier name" />
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Contact <span class="text-red-500">*</span></label>
                <input type="text" wire:model="contact" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Phone or email" />
                @error('contact') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Address <span class="text-red-500">*</span></label>
                <textarea wire:model="address" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" placeholder="Full address"></textarea>
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="rounded-xl border border-stone-200 hover:bg-stone-50 px-5 py-2.5 text-sm font-semibold text-stone-600 transition">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                    <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="save">Create Supplier</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
