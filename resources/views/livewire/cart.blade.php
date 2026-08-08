<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-stone-900">My Cart</h1>
        </div>

        @if ($this->cartItems->isEmpty())
            <div class="bg-white rounded-2xl border border-stone-200 text-center py-20">
                <span class="inline-flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-50 text-brand-600"><i data-lucide="shopping-cart" class="w-8 h-8"></i></span>
                <h2 class="mt-4 text-lg font-semibold text-stone-900">Your cart is empty</h2>
                <p class="text-sm text-stone-500 mt-1">Add some fresh produce to get started.</p>
                <a href="{{ route('shop') }}" wire:navigate class="mt-5 inline-block rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 transition">Start Shopping</a>
            </div>
        @else
            <div class="grid lg:grid-cols-[1fr_360px] gap-8 items-start">
                <div class="bg-white rounded-2xl border border-stone-200 divide-y divide-stone-100">
                    @foreach ($this->cartGroups as $group)
                        @if ($group['recipe'])
                            <div wire:key="recipe-{{ $group['recipe']->id }}">
                                <div class="flex items-center gap-2 px-4 py-3 bg-brand-50/60 border-b border-brand-100">
                                    <a href="{{ route('recipes.show', $group['recipe']) }}" wire:navigate class="flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800">
                                        <i data-lucide="book-open" class="w-4 h-4"></i>
                                        {{ $group['recipe']->title }}
                                    </a>
                                    <span class="text-xs text-stone-400">{{ count($group['items']) }} items</span>
                                </div>
                                @foreach ($group['items'] as $item)
                                    @include('partials.cart-item', ['item' => $item])
                                @endforeach
                            </div>
                        @else
                            @foreach ($group['items'] as $item)
                                @include('partials.cart-item', ['item' => $item])
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <div class="bg-white rounded-2xl border border-stone-200 p-6 lg:sticky lg:top-20">
                    <h2 class="font-semibold text-stone-900 mb-4">Order Summary</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($this->subtotal, 'INR') }}</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>Delivery fee</span>
                            @if ($this->deliveryFee === 0)
                                <span class="font-medium text-green-600">FREE</span>
                            @else
                                <span class="font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($this->deliveryFee, 'INR') }}</span>
                            @endif
                        </div>

                        @if ($this->deliveryFee > 0)
                            <div class="rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-700 inline-flex items-center gap-1.5">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                Add {{ \Illuminate\Support\Number::currency(config('mart.free_delivery_threshold') - $this->subtotal, 'INR') }} more for FREE delivery
                            </div>
                        @endif

                        <div class="border-t border-stone-100 pt-3 flex justify-between">
                            <span class="font-semibold text-stone-900">Total</span>
                            <span class="font-bold text-lg text-stone-900">{{ \Illuminate\Support\Number::currency($this->total, 'INR') }}</span>
                        </div>
                    </div>

                    @if ($this->belowMinimum)
                        <button type="button" disabled class="mt-5 block w-full rounded-xl bg-stone-200 text-stone-500 text-center font-semibold py-3 cursor-not-allowed">
                            Proceed to Checkout
                        </button>
                        <p class="mt-2 text-xs text-red-600 text-center">Minimum order of {{ \Illuminate\Support\Number::currency(config('mart.minimum_order_amount'), 'INR') }} required to checkout.</p>
                    @else
                        <a href="{{ route('checkout') }}" wire:navigate class="mt-5 block w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-center font-semibold py-3 transition">
                            Proceed to Checkout
                        </a>
                    @endif
                    <a href="{{ route('shop') }}" wire:navigate class="mt-2 block w-full rounded-xl border border-stone-200 hover:bg-stone-50 text-center font-medium py-3 text-sm transition">Continue Shopping</a>
                </div>
            </div>
        @endif

        @error('quantity')
            <div class="mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
        @enderror
    </div>
</div>
