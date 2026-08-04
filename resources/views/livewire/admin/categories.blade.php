<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $categories->total() }} categories</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Categories</h2>
        </div>
        <a href="{{ route('admin.categories.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Category
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search categories..." class="w-full sm:w-80 rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
        </div>

        @error('delete')
            <div class="px-4 py-3 bg-red-50 border-b border-red-200 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-stone-50" wire:key="cat-{{ $category->id }}">
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
                                    <a href="{{ route('admin.categories.edit', $category) }}" wire:navigate class="text-brand-600 hover:text-brand-700 font-medium text-xs">Edit</a>
                                    <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete {{ $category->name }}?" class="text-red-600 hover:text-red-700 font-medium text-xs">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center text-stone-400">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-100">
            {{ $categories->links() }}
        </div>
    </div>
</div>
