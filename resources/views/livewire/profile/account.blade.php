<div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('profile') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-700"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Profile</a>
            <div class="flex items-center gap-4 mt-4">
                <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white"><i data-lucide="user-circle" class="w-7 h-7"></i></span>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">Account Details</h1>
                    <p class="text-sm text-stone-500">Update your name and email.</p>
                </div>
            </div>
        </div>

        <section class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-stone-900">Personal Information</h2>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600"><i data-lucide="shield-check" class="w-4 h-4"></i> Verified</span>
            </div>

            <form wire:submit="saveProfile" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Full name <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-stone-700 mb-1">Email (optional)</label>
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        placeholder="you@example.com"
                        class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-stone-700 mb-1">Phone</label>
                    <div class="relative">
                        <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                        <input
                            id="phone"
                            type="text"
                            value="{{ auth()->user()->phone }}"
                            disabled
                            class="w-full rounded-xl border border-stone-200 bg-stone-50 text-stone-500 pl-9 pr-3 py-2.5 text-sm"
                        />
                    </div>
                    <p class="mt-1 text-xs text-stone-400">Your phone number is your login and cannot be changed.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold px-5 py-2.5 transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="saveProfile" class="w-4 h-4" />
                        <span wire:loading.remove.inline-flex wire:target="saveProfile" class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i> Save Changes</span>
                        <span wire:loading wire:target="saveProfile">Saving...</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>