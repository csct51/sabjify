<div>
    <div class="max-w-2xl">
        <nav class="text-sm text-stone-400 mb-4">
            <a href="{{ route('admin.categories.index') }}" wire:navigate class="hover:text-brand-600">← Categories</a>
        </nav>

        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-6">{{ $category ? 'Edit Category' : 'Add Category' }}</h2>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Category Name</label>
                    <input wire:model="name" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Slug</label>
                    <input wire:model="slug" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Sort Order</label>
                        <input wire:model="sort_order" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                        <select wire:model="is_active" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                            <option value="1">Active</option>
                            <option value="0">Hidden</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Image</label>
                    <div class="flex items-center gap-4">
                        @if ($existingImage)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($existingImage) }}" alt="Current category image" class="w-16 h-16 rounded-xl object-cover border border-stone-200">
                        @endif
                        <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.categories.index') }}" wire:navigate class="rounded-xl border border-stone-200 px-5 py-2.5 text-sm font-medium hover:bg-stone-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition">
                        {{ $category ? 'Save Changes' : 'Create Category' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
