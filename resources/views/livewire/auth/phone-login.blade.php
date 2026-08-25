<div>
    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-1.5 mb-4 text-sm text-stone-500 hover:text-stone-700 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to home
    </a>

    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if ($this->step === 'phone')
        <form wire:submit="sendOtp" class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">Login / Sign up</h2>
                <p class="mt-1 text-sm text-stone-500">Enter your 10-digit mobile number to continue.</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 mb-1">Mobile Number <span class="text-red-500">*</span></label>
                <div class="flex rounded-xl border border-stone-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-100 overflow-hidden">
                    <span class="flex items-center px-3 bg-stone-50 border-r border-stone-200 text-sm text-stone-500">+91</span>
                    <input
                        wire:model="phone"
                        id="phone"
                        type="tel"
                        inputmode="numeric"
                        maxlength="10"
                        placeholder="98765 43210"
                        autocomplete="tel"
                        class="flex-1 px-3 py-3 text-base outline-none"
                    >
                </div>
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="sendOtp" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition disabled:opacity-70">
                <x-loading-spinner wire:loading wire:target="sendOtp" class="w-4 h-4" />
                <span wire:loading.remove wire:target="sendOtp">Send OTP</span>
                <span wire:loading wire:target="sendOtp">Sending...</span>
            </button>

            <p class="text-center text-xs text-stone-400">
                By continuing you agree to our Terms & Privacy Policy.
            </p>
        </form>
    @else
        <form wire:submit="verifyOtp" class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-stone-900">
                    {{ $this->isNewUser ? 'Create your account' : 'Verify OTP' }}
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    We sent a 6-digit OTP to <span class="font-medium text-stone-700">+91 {{ $phone }}</span>
                </p>
            </div>

            @if ($this->isNewUser)
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Your Name <span class="text-red-500">*</span></label>
                    <input
                        wire:model="name"
                        id="name"
                        type="text"
                        placeholder="e.g. Rahul Sharma"
                        autocomplete="name"
                        class="w-full rounded-xl border border-stone-300 px-3 py-3 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label for="otp" class="block text-sm font-medium text-stone-700 mb-1">Enter OTP <span class="text-red-500">*</span></label>
                <input
                    wire:model="otp"
                    id="otp"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    class="w-full rounded-xl border border-stone-300 px-3 py-3 text-center text-2xl tracking-[0.5em] outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                >
                @error('otp')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if ($devOtp)
                    <div class="mt-2 rounded-xl bg-brand-50 border border-brand-200 px-3 py-2 text-xs text-brand-700">
                        Dev mode: your OTP is <span class="font-mono font-bold tracking-widest">{{ $devOtp }}</span>
                    </div>
                @endif
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="verifyOtp" class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 transition disabled:opacity-70">
                <x-loading-spinner wire:loading wire:target="verifyOtp" class="w-4 h-4" />
                <span wire:loading.remove wire:target="verifyOtp">{{ $this->isNewUser ? 'Create Account & Login' : 'Verify & Login' }}</span>
                <span wire:loading wire:target="verifyOtp">Verifying...</span>
            </button>

            <div class="flex items-center justify-between text-sm">
                <button type="button" wire:click="goBack" class="text-stone-500 hover:text-stone-700">← Change number</button>
                <button type="button" wire:click="resendOtp" wire:loading.attr="disabled" wire:target="resendOtp" class="inline-flex items-center gap-1.5 text-brand-600 hover:text-brand-700 font-medium disabled:opacity-50">
                    <x-loading-spinner wire:loading wire:target="resendOtp" class="w-3 h-3" />
                    <span wire:loading.remove wire:target="resendOtp">Resend OTP</span>
                    <span wire:loading wire:target="resendOtp">Resending...</span>
                </button>
            </div>
        </form>
    @endif
</div>
