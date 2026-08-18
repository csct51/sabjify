<div
    x-data="{
        open: false,
        hideTimer: null,
        side: 'right',
        pos: { left: 0, top: 0 },
        name: '',
        count: 0,
        url: '#',
        items: [],
        show(event) {
            clearTimeout(this.hideTimer);
            const { name, count, url, items, rect } = event.detail;
            const gap = 12;
            const width = 256;
            const height = Math.min(320, 80 + items.length * 44);
            const spaceRight = window.innerWidth - rect.right;
            const spaceLeft = rect.left;
            const spaceBottom = window.innerHeight - rect.bottom;
            const spaceTop = rect.top;

            let x;
            let y;

            if (spaceRight >= width + gap) {
                this.side = 'right';
                x = rect.right + gap;
                y = this.clamp(rect.top + rect.height / 2 - height / 2, 8, window.innerHeight - height - 8);
            } else if (spaceLeft >= width + gap) {
                this.side = 'left';
                x = rect.left - gap - width;
                y = this.clamp(rect.top + rect.height / 2 - height / 2, 8, window.innerHeight - height - 8);
            } else if (spaceBottom >= height + gap) {
                this.side = 'bottom';
                x = this.clamp(rect.left + rect.width / 2 - width / 2, 8, window.innerWidth - width - 8);
                y = rect.bottom + gap;
            } else {
                this.side = 'top';
                x = this.clamp(rect.left + rect.width / 2 - width / 2, 8, window.innerWidth - width - 8);
                y = rect.top - gap - height;
            }

            this.name = name;
            this.count = count;
            this.url = url;
            this.items = items;
            this.pos = { left: x, top: y };
            this.open = true;
        },
        hide() {
            clearTimeout(this.hideTimer);
            this.hideTimer = setTimeout(() => { this.open = false; }, 150);
        },
        scrollClose(event) {
            if (this.$refs.tooltip && this.$refs.tooltip.contains(event.target)) {
                return;
            }
            clearTimeout(this.hideTimer);
            this.open = false;
        },
        clamp(value, min, max) {
            return Math.min(Math.max(value, min), max);
        },
    }"
    x-on:basket-preview:open.window="show($event)"
    x-on:basket-preview:close.window="hide()"
    x-on:scroll.window.capture="scrollClose($event)"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        x-on:mouseenter="clearTimeout(hideTimer)"
        x-on:mouseleave="hide()"
        x-ref="tooltip"
        role="tooltip"
        class="fixed z-50 w-64 rounded-xl border border-stone-200 bg-white shadow-xl"
        :style="`left:${pos.left}px; top:${pos.top}px;`"
    >
        <span
            class="absolute h-3 w-3 rotate-45 border-stone-200 bg-white"
            :class="{
                'border-l border-t -left-1.5 top-1/2 -translate-y-1/2': side === 'right',
                'border-b border-r -right-1.5 top-1/2 -translate-y-1/2': side === 'left',
                'border-l border-b -top-1.5 left-1/2 -translate-x-1/2': side === 'bottom',
                'border-t border-r -bottom-1.5 left-1/2 -translate-x-1/2': side === 'top',
            }"
        ></span>

        <div class="border-b border-stone-100 px-4 py-2.5">
            <p class="text-sm font-semibold text-stone-900 truncate" x-text="name"></p>
            <p class="text-xs text-stone-400"><span x-text="count"></span> items</p>
        </div>

        <ul class="max-h-64 overflow-y-auto py-1">
            <template x-for="item in items" :key="item.name">
                <li class="px-4 py-1.5 text-sm text-stone-700">
                    <span class="block truncate" x-text="item.name"></span>
                    <span class="block text-xs text-stone-400" x-text="item.unit"></span>
                </li>
            </template>
        </ul>

        <a :href="url" wire:navigate class="flex items-center gap-1 border-t border-stone-100 px-4 py-2.5 text-sm font-semibold text-brand-600 hover:bg-brand-50 rounded-b-xl transition">
            View basket
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>