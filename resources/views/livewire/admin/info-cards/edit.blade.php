<div>
    <nav class="text-sm text-stone-400 mb-4">
        <a href="{{ route('admin.info-cards.index') }}" wire:navigate class="hover:text-brand-600">← Info Cards</a>
    </nav>

    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <h2 class="text-lg font-semibold text-stone-900 mb-1">Edit Card #{{ $position }}</h2>
        <p class="text-sm text-stone-500 mb-6">Icons come from the built-in set, so changes apply instantly.</p>

        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Icon <span class="text-red-500">*</span></label>
                <x-admin.searchable-select
                    target="icon"
                    :options="\App\Support\InfoCards::iconOptions()"
                    :selected="$icon"
                    placeholder="Select icon..."
                    search-placeholder="Search icons..."
                />
                @error('icon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input wire:model="title" type="text" maxlength="60" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Subtitle</label>
                <input wire:model="subtitle" type="text" maxlength="80" placeholder="Optional" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                @error('subtitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.info-cards.index') }}" wire:navigate class="rounded-xl border border-stone-200 hover:bg-stone-50 px-5 py-2.5 text-sm font-semibold text-stone-600 transition">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                    <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="save">Save Card</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
