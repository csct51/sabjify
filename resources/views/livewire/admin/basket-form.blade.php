<div>
    <div>
        <nav class="text-sm text-stone-400 mb-4">
            <a href="{{ route('admin.baskets.index') }}" wire:navigate class="hover:text-brand-600">← Baskets</a>
        </nav>

        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-6">{{ $basket ? 'Edit Basket' : 'Add Basket' }}</h2>

            <form wire:submit="save" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Basket Name <span class="text-red-500">*</span></label>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Type <span class="text-red-500">*</span></label>
                        <select wire:model="type" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                            @foreach (\App\Models\Basket::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Basket Price <span class="text-red-500">*</span></label>
                        <input wire:model="price" type="number" min="1" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="mt-1 text-[11px] text-stone-400">The purchase price users will see and pay.</p>
                        @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"></textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Products <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <div class="rounded-xl border border-stone-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 overflow-hidden">
                                <div class="relative border-b border-stone-100">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                    </span>
                                    <input
                                        wire:model.live.debounce.200ms="productSearch"
                                        type="text"
                                        placeholder="Search products..."
                                        class="w-full pl-9 pr-3 py-2.5 text-sm outline-none"
                                    >
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-stone-100">
                                    @forelse ($this->products as $product)
                                        <label class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-stone-50">
                                            <input type="checkbox" wire:model.live="productIds" value="{{ $product->id }}" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                                            <span class="flex items-center gap-2 min-w-0 flex-1">
                                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                                    <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-medium text-stone-800 truncate">{{ $product->name }}</span>
                                                    <span class="block text-xs text-stone-400">{{ $product->category?->name }}</span>
                                                </span>
                                            </span>
                                        </label>
                                    @empty
                                        <p class="px-3 py-4 text-sm text-stone-400">@if ($productSearch !== '')No products found for "{{ $productSearch }}".@else No active products found.@endif</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-brand-200 bg-brand-50/40 flex flex-col overflow-hidden">
                            <div class="flex items-center justify-between px-3 py-2.5 border-b border-brand-100 bg-white/60">
                                <p class="text-sm font-semibold text-stone-800">Selected Products</p>
                                <span class="inline-flex items-center rounded-full bg-brand-100 text-brand-700 text-xs font-semibold px-2.5 py-0.5">{{ count($productIds) }} selected</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-stone-100">
                                @forelse ($this->selectedProducts as $product)
                                    <div class="px-3 py-2.5">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-stone-200 shrink-0 overflow-hidden">
                                                <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-sm font-medium text-stone-800 truncate">{{ $product->name }}</span>
                                                <span class="block text-xs text-stone-400">{{ $product->category?->name }} · {{ \Illuminate\Support\Number::currency($product->price, 'INR') }}/{{ $product->unit }}</span>
                                            </span>
                                            <button type="button" wire:click="removeProduct({{ $product->id }})" class="flex items-center justify-center w-7 h-7 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 transition" aria-label="Remove {{ $product->name }}">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2 grid grid-cols-2 gap-3 pl-11">
                                            <div>
                                                <label class="block text-[11px] font-medium text-stone-500 mb-1">Basket unit <span class="text-stone-400">(packaging)</span></label>
                                                <input wire:model="units.{{ $product->id }}" type="text" placeholder="e.g. 250g, 1 box" class="w-full rounded-lg border border-stone-300 px-2.5 py-1.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                @error('units.{{ $product->id }}')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-medium text-stone-500 mb-1">Basket price <span class="text-stone-400">(packaging)</span></label>
                                                <input wire:model="prices.{{ $product->id }}" type="number" min="0" placeholder="e.g. 40" class="w-full rounded-lg border border-stone-300 px-2.5 py-1.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                                                @error('prices.{{ $product->id }}')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="px-3 py-4 text-sm text-stone-400">No products selected yet.</p>
                                @endforelse
                            </div>
                            <div class="px-3 py-2.5 border-t border-brand-100 bg-white/60">
                                <p class="text-[11px] text-stone-400">Units and prices set here are only packaging notes shown as the basket contents. They do not change the shop price of these products.</p>
                            </div>
                        </div>
                    </div>
                    @error('productIds')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Basket Image</label>
                    <div class="flex items-center gap-4">
                        @if ($basket?->imageUrl())
                            <img src="{{ $basket->imageUrl() }}" alt="Current basket image" class="w-16 h-16 rounded-xl object-cover border border-stone-200">
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
                    <a href="{{ route('admin.baskets.index') }}" wire:navigate class="rounded-xl border border-stone-200 px-5 py-2.5 text-sm font-medium hover:bg-stone-50">Cancel</a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="save">{{ $basket ? 'Save Changes' : 'Create Basket' }}</span>
                        <span wire:loading wire:target="save">{{ $basket ? 'Saving...' : 'Creating...' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
