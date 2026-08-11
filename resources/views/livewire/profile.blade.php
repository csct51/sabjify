<div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-4 mb-8">
            <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="user" class="w-7 h-7"></i></span>
            <div>
                <h1 class="text-2xl font-bold text-stone-900">My Profile</h1>
                <p class="text-sm text-stone-500">Manage your account, addresses and orders.</p>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-stone-200 p-6 mb-6">
            <div class="flex items-center gap-4">
                <span class="flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-100 text-brand-700 text-xl font-bold">{{ auth()->user()->initials() }}</span>
                <div class="min-w-0">
                    <p class="font-semibold text-stone-900 text-lg truncate">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-stone-500 truncate">{{ auth()->user()->phone }}</p>
                    @if (auth()->user()->email)
                        <p class="text-sm text-stone-500 truncate">{{ auth()->user()->email }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-5 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                    <p class="text-lg font-bold text-stone-900">{{ $this->ordersCount }}</p>
                    <p class="text-xs text-stone-500">Orders</p>
                </div>
                <div class="rounded-xl bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                    <p class="text-lg font-bold text-stone-900">{{ $this->addressesCount }}</p>
                    <p class="text-xs text-stone-500">Addresses</p>
                </div>
                <div class="rounded-xl bg-stone-50 border border-stone-100 px-4 py-3 text-center">
                    <p class="text-lg font-bold text-stone-900">{{ auth()->user()->created_at->format('M Y') }}</p>
                    <p class="text-xs text-stone-500">Since</p>
                </div>
            </div>
        </section>

        <nav class="space-y-3">
            <a href="{{ route('profile.account') }}" wire:navigate class="flex items-center gap-4 rounded-2xl border border-stone-200 hover:border-brand-300 bg-white px-5 py-4 transition">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="user-circle" class="w-5 h-5"></i></span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-stone-900">Account Details</p>
                    <p class="text-sm text-stone-500 truncate">Name, email & phone</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-stone-400"></i>
            </a>

            <a href="{{ route('profile.addresses') }}" wire:navigate class="flex items-center gap-4 rounded-2xl border border-stone-200 hover:border-brand-300 bg-white px-5 py-4 transition">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-stone-900">Saved Addresses</p>
                    <p class="text-sm text-stone-500 truncate">{{ $this->addressesCount }} saved address{{ $this->addressesCount === 1 ? '' : 'es' }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-stone-400"></i>
            </a>

            <a href="{{ route('orders.index') }}" wire:navigate class="flex items-center gap-4 rounded-2xl border border-stone-200 hover:border-brand-300 bg-white px-5 py-4 transition">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-brand-50 text-brand-600"><i data-lucide="package" class="w-5 h-5"></i></span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-stone-900">My Orders</p>
                    <p class="text-sm text-stone-500 truncate">{{ $this->ordersCount }} order{{ $this->ordersCount === 1 ? '' : 's' }}</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-stone-400"></i>
            </a>

            <button type="button" @click="$dispatch('confirm-modal', { message: 'Are you sure you want to log out?', action: () => $wire.logout() })" class="w-full flex items-center gap-4 rounded-2xl border border-red-200 hover:border-red-300 hover:bg-red-50 bg-white px-5 py-4 transition">
                <span class="flex items-center justify-center w-11 h-11 rounded-xl bg-red-50 text-red-600"><i data-lucide="log-out" class="w-5 h-5"></i></span>
                <div class="flex-1 min-w-0 text-left">
                    <p class="font-medium text-red-600">Logout</p>
                    <p class="text-sm text-red-400">Sign out of your account</p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-red-400"></i>
            </button>
        </nav>
    </div>
</div>