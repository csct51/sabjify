@props(['initialMessage' => null, 'initialType' => 'success'])

<div
    x-data="{
        toasts: [],
        init() {
            if (@js($initialMessage)) {
                this.add({ message: @js($initialMessage), type: @js($initialType) });
            }
        },
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type: detail.type ?? 'success', message: detail.message, visible: false });
            setTimeout(() => { const t = this.toasts.find((x) => x.id === id); if (t) t.visible = true; }, 10);
            setTimeout(() => {
                const t = this.toasts.find((x) => x.id === id);
                if (t) t.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter((x) => x.id !== id); }, 300);
            }, 3500);
        },
    }"
    x-on:toast.window="add($event.detail)"
    x-cloak
    data-store-toast
    class="fixed inset-0 z-[90] flex items-center justify-center p-4 pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="flex flex-col items-center text-center gap-3 rounded-2xl border border-stone-200 bg-white px-8 py-6 shadow-xl w-72 max-w-[80vw]"
            role="status"
        >
            <span class="flex items-center justify-center w-14 h-14 rounded-full" :class="toast.type === 'error' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'">
                <svg x-show="toast.type !== 'error'" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path></svg>
                <svg x-show="toast.type === 'error'" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </span>
            <span x-text="toast.message" class="text-sm font-medium text-stone-700"></span>
        </div>
    </template>
</div>
