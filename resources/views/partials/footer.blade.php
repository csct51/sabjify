<footer class="bg-white border-t border-stone-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <h3 class="font-semibold text-stone-900">{{ config('app.name') }}</h3>
            <p class="mt-3 text-sm text-stone-500 leading-relaxed">
                Farm-fresh vegetables and fruits delivered to your doorstep. Handpicked daily from trusted local growers.
            </p>
        </div>
        <div>
            <h4 class="font-medium text-sm text-stone-900 uppercase tracking-wide">Shop</h4>
            <ul class="mt-3 space-y-2 text-sm text-stone-500">
                <li><a href="{{ route('shop') }}" class="hover:text-brand-600">All Products</a></li>
                @foreach (\App\Models\Category::active()->orderBy('sort_order')->limit(5)->get() as $category)
                    <li><a href="{{ route('shop', ['category' => $category->slug]) }}" class="hover:text-brand-600">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4 class="font-medium text-sm text-stone-900 uppercase tracking-wide">My Account</h4>
            <ul class="mt-3 space-y-2 text-sm text-stone-500">
                @auth
                    <li><a href="{{ route('orders.index') }}" class="hover:text-brand-600">My Orders</a></li>
                    <li><a href="{{ route('cart') }}" class="hover:text-brand-600">My Cart</a></li>
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-brand-600">Login</a></li>
                @endauth
            </ul>
        </div>
        <div>
            <h4 class="font-medium text-sm text-stone-900 uppercase tracking-wide">Contact</h4>
            <ul class="mt-3 space-y-2 text-sm text-stone-500">
                <li>Call us: <a href="tel:+919876543210" class="hover:text-brand-600">+91 98765 43210</a></li>
                <li>Support: 9am - 9pm, all days</li>
            </ul>
        </div>
    </div>
    <div class="border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-stone-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>
