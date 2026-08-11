<div>
    <div>
        <nav class="text-sm text-stone-400 mb-4">
            <a href="{{ route('admin.products.index') }}" wire:navigate class="hover:text-brand-600">← Products</a>
        </nav>

        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-6">{{ $product ? 'Edit Product' : 'Add Product' }}</h2>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select wire:model="categoryId" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                        <option value="">Select category</option>
                        @foreach ($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input wire:model.live="name" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @if (! $slugManuallyEdited && $this->autoSlug !== '')
                            <p class="mt-1 text-[11px] text-stone-400">Slug: <span class="font-medium text-brand-600">{{ $this->autoSlug }}</span></p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Slug <span class="text-red-500">*</span></label>
                        <input wire:model="slug" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1 text-[11px] text-stone-400">Auto-generated from the name. Edit it to override.</p>
                        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Unit <span class="text-red-500">*</span></label>
                        <select wire:model="unit" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                            @foreach ($this->units as $unit)
                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                        <input wire:model="stock" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Selling Price (₹) <span class="text-red-500">*</span></label>
                        <input wire:model="price" type="number" min="1" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">MRP (₹) <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input wire:model="mrp" type="number" min="1" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('mrp')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                        <select wire:model="is_active" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                            <option value="1" @selected($is_active === '1')>Active</option>
                            <option value="0" @selected($is_active === '0')>Hidden</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Sort Order <span class="text-red-500">*</span></label>
                        <input wire:model="sort_order" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input wire:model="is_featured" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                    Featured product <span class="text-stone-400 text-xs">(shown on homepage)</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Product Image</label>
                    <div class="flex items-center gap-4">
                        @if ($product?->imageUrl())
                            <img src="{{ $product->imageUrl() }}" alt="Current product image" class="w-16 h-16 rounded-xl object-cover border border-stone-200">
                        @endif
                        <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                    <div class="mt-3">
                        <label class="block text-xs font-medium text-stone-500 mb-1">...or paste an image link</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">
                                    <i data-lucide="link"></i>
                                </span>
                                <input
                                    wire:model.live="imageUrl"
                                    type="url"
                                    placeholder="https://example.com/image.jpg"
                                    class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                >
                            </div>
                            @if ($imageUrl)
                                <img src="{{ $imageUrl }}" alt="Image preview" class="w-10 h-10 rounded-lg object-cover border border-stone-200 shrink-0">
                            @endif
                        </div>
                        @error('imageUrl')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-stone-400">You can either upload a file above or provide an external image URL. A file upload takes priority.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.products.index') }}" wire:navigate class="rounded-xl border border-stone-200 px-5 py-2.5 text-sm font-medium hover:bg-stone-50">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="save">{{ $product ? 'Save Changes' : 'Create Product' }}</span>
                        <span wire:loading wire:target="save">{{ $product ? 'Saving...' : 'Creating...' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
