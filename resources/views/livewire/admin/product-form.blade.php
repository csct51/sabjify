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
                    <label class="block text-sm font-medium text-stone-700 mb-1">Alternate Names</label>
                    <textarea wire:model="alternateNames" rows="2" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    <p class="mt-1 text-[11px] text-stone-400">Local or alternate names (e.g. टमाटर, tamatar), separated by commas. Spaces are allowed within a name (e.g. rock salt).</p>
                    @error('alternateNames')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                @php $lowStockUnit = $this->formPurchaseUnit(); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Base Unit <span class="text-red-500">*</span></label>
                        <select wire:model.live="baseUnit" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white mb-3">
                            @foreach ($this->baseOptions as $option)
                                <option value="{{ $option->name }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                        @error('baseUnit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-stone-400">All units for this product must share this base (e.g. 1 kg, 500 g both g). To add a base unit, create (or tick) a Unit row as Is-base with its purchase unit.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Low stock alert at ({{ $lowStockUnit }})</label>
                        <input wire:model="lowStock" type="number" min="0" step="0.001" placeholder="{{ \App\Models\Unit::qtyPlaceholderFor($lowStockUnit) }}" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('lowStock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-stone-400">Notify when stock falls to this level. Blank = no low alert.</p>
                    </div>
                </div>

                @if (! $product)
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Opening Stock ({{ $baseUnit }}) <span class="text-red-500">*</span></label>
                        <input wire:model="currentStock" type="number" min="0" step="0.001" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('currentStock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-stone-400">Starting stock in base units. Later movements go through purchases and wastage.</p>
                    </div>
                @endif

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-stone-700">Units & Prices <span class="text-red-500">*</span></label>
                        <button type="button" wire:click="addUnitRow" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            Add Unit
                        </button>
                    </div>

                    @foreach ($unitRows as $index => $row)
                        <div class="rounded-xl border border-stone-200 p-3 mb-3" wire:key="unit-row-{{ $index }}">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-medium text-stone-500 mb-1">Unit</label>
                                    <select wire:model="unitRows.{{ $index }}.unit" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                                        <option value="">Select unit</option>
                                        @foreach ($this->units as $unit)
                                            <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unitRows.'.$index.'.unit')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-stone-500 mb-1">Selling Price (₹)</label>
                                    <input wire:model="unitRows.{{ $index }}.price" type="number" min="1" placeholder="e.g. 120" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('unitRows.'.$index.'.price')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-stone-500 mb-1">MRP (₹) <span class="text-stone-400">(optional)</span></label>
                                    <input wire:model="unitRows.{{ $index }}.mrp" type="number" min="1" placeholder="e.g. 150" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                    @error('unitRows.'.$index.'.mrp')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <label class="mt-2 inline-flex items-center gap-2 text-xs font-medium text-stone-600">
                                <input wire:model="unitRows.{{ $index }}.in_stock" type="checkbox" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                In stock <span class="text-stone-400">(available to customers)</span>
                            </label>
                            @if (count($unitRows) > 1)
                                <button type="button" wire:click="removeUnitRow({{ $index }})" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    Remove unit
                                </button>
                            @endif
                        </div>
                    @endforeach

                    @error('unitRows')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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
                            <img src="{{ str_replace('/storage/', '/public/storage/', $product->imageUrl()) }}" alt="Current product image" class="w-16 h-16 rounded-xl object-cover border border-stone-200">
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
