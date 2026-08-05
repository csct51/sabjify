<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $categories->count() }} categories</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Categories</h2>
        </div>
        <a href="{{ route('admin.categories.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Category
        </a>
    </div>

    @error('delete')
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="categories-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($categories as $category)
                        <tr class="hover:bg-stone-50" wire:key="cat-{{ $category->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 text-stone-400">
                                        <i data-lucide="folder" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <p class="font-medium text-stone-900">{{ $category->name }}</p>
                                        <p class="text-xs text-stone-400">Sort: {{ $category->sort_order }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $category->products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $category->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $category->is_active ? 'text-green-600' : 'text-stone-400' }}">
                                    <span class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    {{ $category->is_active ? 'Active' : 'Hidden' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">Edit</a>
                                    <button type="button" data-confirm-message="Delete {{ $category->name }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $category->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
