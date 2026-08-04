<div>
    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-1">Platform Settings</h2>
            <p class="text-sm text-stone-500 mb-6">Manage how your store appears to customers.</p>

            @if (session('success'))
                <div class="mb-5 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <form wire:submit="save" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Store Name</label>
                    <input wire:model="storeName" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('storeName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Delivery Fee (₹)</label>
                        <input wire:model="deliveryFee" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('deliveryFee')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Free Delivery Above (₹)</label>
                        <input wire:model="freeDeliveryThreshold" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('freeDeliveryThreshold')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Placeholder Image URL</label>
                    <input wire:model="placeholderImage" type="url" placeholder="https://placehold.co/600x600/F0FDF4/166534" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('placeholderImage')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
