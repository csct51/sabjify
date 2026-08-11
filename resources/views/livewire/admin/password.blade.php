<div>
    <div class="max-w-xl">
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-semibold text-stone-900 mb-1">Change Password</h2>
            <p class="text-sm text-stone-500 mb-6">Update the password used to sign in to the admin panel.</p>

            <form wire:submit="updatePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Current Password <span class="text-red-500">*</span></label>
                    <input wire:model="currentPassword" type="password" autocomplete="current-password" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('currentPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">New Password <span class="text-red-500">*</span></label>
                    <input wire:model="newPassword" type="password" autocomplete="new-password" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    <p class="text-xs text-stone-500 mt-1">At least 8 characters.</p>
                    @error('newPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                    <input wire:model="newPasswordConfirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100">
                    @error('newPassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 text-sm font-semibold transition disabled:opacity-70">
                        <x-loading-spinner wire:loading wire:target="updatePassword" class="w-4 h-4" />
                        <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                        <span wire:loading wire:target="updatePassword">Updating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
