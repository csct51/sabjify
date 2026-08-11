<div
    x-data="{
        open: false,
        message: '',
        action: null,
        loading: false,
        async confirm() {
            this.loading = true;
            try {
                await this.action?.();
            } finally {
                this.open = false;
                this.loading = false;
            }
        },
    }"
    x-on:confirm-modal.window="open = true; message = $event.detail.message; action = $event.detail.action"
    x-cloak
>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-auto min-w-[260px] max-w-xs p-6" x-transition @keydown.escape.window="open = false">
            <p class="text-sm font-medium text-stone-800" x-text="message"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" :disabled="loading" class="rounded-lg border border-stone-200 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50 disabled:opacity-50">Cancel</button>
                <button type="button" @click="confirm()" :disabled="loading" class="inline-flex items-center gap-2 rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold disabled:opacity-70">
                    <svg x-show="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Deleting...' : 'Confirm'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
