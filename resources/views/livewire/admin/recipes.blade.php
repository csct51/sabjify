<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ $recipes->count() }} recipes</p>
            <h2 class="text-lg font-semibold text-stone-900">Manage Recipes</h2>
        </div>
        <a href="{{ route('admin.recipes.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Recipe
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="recipes-table" data-datatable class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Recipe</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($recipes as $recipe)
                        <tr class="hover:bg-stone-50" wire:key="recipe-{{ $recipe->id }}">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-brand-50 to-lime-100 shrink-0 overflow-hidden">
                                        <img src="{{ $recipe->displayImageUrl() }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover">
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.recipes.edit', $recipe) }}" wire:navigate class="font-medium text-stone-900 hover:text-brand-700">{{ $recipe->title }}</a>
                                        <p class="text-xs text-stone-400">Sort: {{ $recipe->sort_order }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-stone-600">{{ $recipe->products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleActive({{ $recipe->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $recipe->is_active ? 'text-green-600' : 'text-stone-400' }}">
                                    <span class="w-2 h-2 rounded-full {{ $recipe->is_active ? 'bg-green-500' : 'bg-stone-300' }}"></span>
                                    {{ $recipe->is_active ? 'Active' : 'Hidden' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('admin.recipes.edit', $recipe) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium text-stone-600">Edit</a>
                                    <button type="button" data-confirm-message="Delete {{ $recipe->title }}?" @click="$dispatch('confirm-modal', { message: $el.dataset.confirmMessage, action: () => $wire.delete({{ $recipe->id }}) })" class="rounded-lg border border-red-200 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
