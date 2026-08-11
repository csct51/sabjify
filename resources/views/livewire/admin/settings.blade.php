<div>
    <div>
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-1">Platform Settings</h2>
            <p class="text-sm text-stone-500 mb-6">Manage how your store appears to customers.</p>

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
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Minimum Order Amount (₹) <span class="text-red-500">*</span></label>
                        <input wire:model="minimumOrderAmount" type="number" min="0" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                        <p class="text-xs text-stone-500 mt-1">Carts below this subtotal cannot proceed to checkout.</p>
                        @error('minimumOrderAmount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Store Logo</label>
                    <p class="text-xs text-stone-500 mb-3">Shown in the store header, admin panel, and auth pages. Leave on "Default icon" to use the built-in icon.</p>

                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <label class="flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm cursor-pointer transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" wire:model.live="logoType" value="icon" class="text-brand-600 focus:ring-brand-500">
                            Default icon
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm cursor-pointer transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" wire:model.live="logoType" value="image" class="text-brand-600 focus:ring-brand-500">
                            Upload image
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-stone-200 px-4 py-2.5 text-sm cursor-pointer transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" wire:model.live="logoType" value="url" class="text-brand-600 focus:ring-brand-500">
                            Use URL
                        </label>
                    </div>

                    @if ($logoType === 'image')
                        <div>
                            <input wire:model="logoImage" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm">
                            <p class="text-xs text-stone-500 mt-1">JPG, PNG or WebP up to 10MB. Uploading a new file replaces the current logo.</p>
                            @error('logoImage')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    @if ($logoType === 'url')
                        <div>
                            <input wire:model="logoUrl" type="url" placeholder="https://example.com/logo.png" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                            @error('logoUrl')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="flex items-center gap-3 mt-4">
                        <x-logo :src="$logoImage?->temporaryUrl()" class="w-12 h-12 rounded-xl shadow-sm" icon="w-6 h-6" />
                        <span class="text-xs text-stone-400">Logo preview</span>
                    </div>
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
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="save" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="save">Save Settings</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
