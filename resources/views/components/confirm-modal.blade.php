<div
    x-data="{
        open: false,
        message: '',
        action: null,
    }"
    x-on:confirm-modal.window="open = true; message = $event.detail.message; action = $event.detail.action"
    x-cloak
>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-auto min-w-[260px] max-w-xs p-6" x-transition @keydown.escape.window="open = false">
            <p class="text-sm font-medium text-stone-800" x-text="message"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" class="rounded-lg border border-stone-200 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50">Cancel</button>
                <button type="button" @click="action?.(); open = false" class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold">Confirm</button>
            </div>
        </div>
    </div>
</div>
