<div class="bg-white/70 backdrop-blur-md border-b border-stone-200/80 sticky top-0 z-40 supports-[backdrop-filter]:bg-white/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                <img src="{{ asset('logo.svg') }}" alt="Sabjify" class="h-16 w-auto">
            </a>

            <div
                x-data="{
                    detect() {
                        if (! navigator.geolocation) {
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                $wire.call('detectLocation', position.coords.latitude, position.coords.longitude);
                                window.dispatchEvent(new CustomEvent('geolocation:permission-granted'));
                            },
                            (error) => {
                                if (error && error.code === 1) {
                                    window.dispatchEvent(new CustomEvent('geolocation:permission-denied'));
                                }
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
                        );
                    }
                }"
                x-init="@if ($needsDetection) detect(); @endif"
                class="ml-2 min-w-0 max-w-[55vw] sm:max-w-[60vw] lg:max-w-[none] flex items-center"
            >
                @if ($hasLocation)
                    <button type="button" wire:click="openAddressPrompt" class="inline-flex items-center gap-2 rounded bg-stone-100 px-4 py-2 text-sm text-stone-700 hover:bg-stone-200 transition min-w-0 max-w-full">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0 {{ $deliveryAvailable === true ? 'text-green-600' : ($deliveryAvailable === false ? 'text-amber-500' : 'text-stone-500') }}"></i>
                        <span class="truncate min-w-0">{{ $locationLabel }}</span>
                        @if ($deliveryAvailable === true)
                            <span class="hidden sm:inline text-[10px] font-semibold text-green-700 shrink-0">Delivery available</span>
                        @elseif ($deliveryAvailable === false)
                            <span class="hidden sm:inline text-[10px] font-medium text-amber-600">Coming soon</span>
                        @endif
                    </button>
                    <button type="button" @click="detect()" class="ml-1 inline-flex items-center justify-center w-9 h-9 rounded bg-stone-100 text-stone-600 hover:bg-stone-200 transition shrink-0" aria-label="Use my current location" title="Use my current location">
                        <i data-lucide="locate" class="w-4 h-4"></i>
                    </button>
                @else
                    <button type="button" @click="detect()" class="inline-flex items-center gap-2 rounded bg-stone-100 px-4 py-2 text-sm text-stone-700 hover:bg-stone-200 transition min-w-0 max-w-full">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate min-w-0">Use my location</span>
                    </button>
                @endif
            </div>

            <div class="flex items-center">
                <a href="{{ route('search') }}" wire:navigate aria-label="Search" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </a>

                @auth
                    <a href="{{ route('profile') }}" wire:navigate aria-label="Profile" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition {{ request()->routeIs('profile') ? 'text-brand-600' : '' }}">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate aria-label="Login" class="inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 hover:bg-stone-100 transition">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
