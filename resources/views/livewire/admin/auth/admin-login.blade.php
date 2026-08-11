<div>
    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form wire:submit="login" class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-stone-900">Admin Login</h2>
            <p class="mt-1 text-sm text-stone-500">Sign in with your administrator credentials.</p>
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-stone-700 mb-1">Username <span class="text-red-500">*</span></label>
            <input
                wire:model="username"
                id="username"
                type="text"
                autocomplete="username"
                placeholder="admin"
                class="w-full rounded-xl border border-stone-300 px-3 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
            @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-stone-700 mb-1">Password <span class="text-red-500">*</span></label>
            <div class="relative" x-data="{ showPassword: false }">
                <input
                    wire:model="password"
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-stone-300 px-3 py-3 pr-12 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-100"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <i x-show="!showPassword" data-lucide="eye" class="w-5 h-5"></i>
                    <i x-show="showPassword" data-lucide="eye-off" class="w-5 h-5"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="login" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition disabled:opacity-70">
            <x-loading-spinner wire:loading wire:target="login" class="w-4 h-4" />
            <span wire:loading.remove wire:target="login">Sign In</span>
            <span wire:loading wire:target="login">Signing in...</span>
        </button>

        <p class="text-center text-xs text-stone-400">
            <a href="{{ route('home') }}" wire:navigate class="text-brand-600 hover:text-brand-700 font-medium">← Back to store</a>
        </p>
    </form>
</div>
