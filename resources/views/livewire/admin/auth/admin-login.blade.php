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
            <label for="username" class="block text-sm font-medium text-stone-700 mb-1">Username</label>
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
            <label for="password" class="block text-sm font-medium text-stone-700 mb-1">Password</label>
            <input
                wire:model="password"
                id="password"
                type="password"
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full rounded-xl border border-stone-300 px-3 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition">
            Sign In
        </button>

        <p class="text-center text-xs text-stone-400">
            <a href="{{ route('home') }}" wire:navigate class="text-brand-600 hover:text-brand-700 font-medium">← Back to store</a>
        </p>
    </form>
</div>
