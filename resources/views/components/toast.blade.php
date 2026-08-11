@props([
    'initialMessage' => null,
    'initialType' => 'success',
])

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
            const type = detail.type ?? 'success';
            const message = detail.message;
            this.toasts.push({ id, type, message, visible: false });
            setTimeout(() => this.show(id), 10);
            setTimeout(() => this.hide(id), 3500);
        },
        show(id) {
            const toast = this.toasts.find((t) => t.id === id);
            if (toast) {
                toast.visible = true;
            }
        },
        hide(id) {
            const toast = this.toasts.find((t) => t.id === id);
            if (toast) {
                toast.visible = false;
            }
            setTimeout(() => {
                this.toasts = this.toasts.filter((t) => t.id !== id);
            }, 300);
        },
    }"
    x-on:toast.window="add($event.detail)"
    x-cloak
    class="fixed top-4 left-1/2 -translate-x-1/2 z-[80] flex flex-col items-center gap-2 pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="toast.type === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700'"
            class="pointer-events-auto flex items-center gap-2.5 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg max-w-sm"
            role="status"
        >
            <svg x-show="toast.type !== 'error'" class="w-4 h-4 shrink-0 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <path d="M22 4 12 14.01l-3-3"></path>
            </svg>
            <svg x-show="toast.type === 'error'" class="w-4 h-4 shrink-0 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="m15 9-6 6"></path>
                <path d="m9 9 6 6"></path>
            </svg>
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
