<div
    x-data="{
        open: false,
        query: '',
        selectedId: @js($productId),
        all: @js($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category?->name])->values()),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.all;
            return this.all.filter(p => p.name.toLowerCase().includes(q) || (p.category && p.category.toLowerCase().includes(q)));
        },
        label(p) {
            return p.name + (p.category ? ' · ' + p.category : '');
        },
        pick(p) {
            this.selectedId = p.id;
            this.query = this.label(p);
            this.open = false;
            $wire.set('{{ $target }}', p.id);
        },
        clear() {
            this.selectedId = null;
            this.query = '';
            $wire.set('{{ $target }}', null);
        },
        syncFromServer() {
            const found = this.all.find(p => p.id == this.selectedId);
            if (found) {
                this.query = this.label(found);
            }
        }
    }"
    x-init="syncFromServer()"
    @click.outside="open = false"
    class="relative"
>
    <div class="relative">
        <input
            type="text"
            x-model="query"
            @focus="open = true"
            @input="open = true"
            @keydown.escape="open = false"
            @keydown.enter.prevent="if (filtered.length === 1) pick(filtered[0])"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-lg border border-stone-300 pl-3 pr-8 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white"
            autocomplete="off"
        />
        <button
            type="button"
            x-show="query"
            @click="clear()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600"
            aria-label="Clear"
        >
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <div
        x-show="open && filtered.length > 0"
        x-cloak
        class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto rounded-lg border border-stone-200 bg-white shadow-lg"
    >
        <template x-for="p in filtered" :key="p.id">
            <button
                type="button"
                @click="pick(p)"
                class="w-full text-left px-3 py-2 hover:bg-stone-50 text-sm"
                :class="p.id == selectedId ? 'bg-brand-50 text-brand-700' : 'text-stone-700'"
            >
                <span x-text="p.name"></span>
                <template x-if="p.category">
                    <span class="text-stone-400" x-text="' · ' + p.category"></span>
                </template>
            </button>
        </template>
    </div>
    <div
        x-show="open && filtered.length === 0"
        x-cloak
        class="absolute z-20 mt-1 w-full rounded-lg border border-stone-200 bg-white shadow-lg px-3 py-2 text-sm text-stone-500"
    >
        No products found.
    </div>
    <input type="hidden" wire:model="{{ $target }}" />
</div>
