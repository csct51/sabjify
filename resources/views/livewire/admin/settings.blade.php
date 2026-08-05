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
                    <label class="block text-sm font-medium text-stone-700 mb-1">Store Name <span class="text-red-500">*</span></label>
                    <input wire:model="storeName" type="text" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('storeName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Delivery Fee (₹) <span class="text-red-500">*</span></label>
                        <input wire:model="deliveryFee" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('deliveryFee')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Free Delivery Above (₹) <span class="text-red-500">*</span></label>
                        <input wire:model="freeDeliveryThreshold" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        @error('freeDeliveryThreshold')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Placeholder Image URL</label>
                    <input wire:model="placeholderImage" type="url" placeholder="https://placehold.co/600x600/F0FDF4/166534" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('placeholderImage')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-2">Payment Methods <span class="text-red-500">*</span></label>
                    <p class="text-xs text-stone-500 mb-3">Select the payment methods customers can use at checkout.</p>
                    <div class="space-y-2">
                        @foreach (config('mart.payment_methods') as $key => $method)
                            @php
                                $isLastEnabled = count($this->enabledPaymentMethods) === 1 && in_array($key, $this->enabledPaymentMethods, true);
                            @endphp
                            <label class="flex items-center justify-between gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 transition {{ $isLastEnabled ? 'opacity-75' : '' }}">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="{{ $method['icon'] }}" class="w-4 h-4"></i></span>
                                    <span>
                                        <span class="block text-sm font-medium text-stone-900">{{ $method['label'] }}</span>
                                        <span class="block text-xs text-stone-500">{{ $method['description'] }}</span>
                                    </span>
                                </div>
                                <input type="checkbox" wire:model.live="enabledPaymentMethods" value="{{ $key }}" @disabled($isLastEnabled) title="{{ $isLastEnabled ? 'At least one payment method is required' : '' }}" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500">
                            </label>
                        @endforeach
                    </div>
                    @error('enabledPaymentMethods')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
