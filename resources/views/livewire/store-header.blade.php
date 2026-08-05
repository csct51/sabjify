<div class="bg-white border-b border-stone-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-600 text-white shadow-sm"><i data-lucide="leaf" class="w-5 h-5"></i></span>
                <span class="font-bold text-lg text-stone-900">{{ config('app.name') }}</span>
            </a>

            <a href="{{ route('search') }}" wire:navigate class="flex-1 max-w-md mx-auto block">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400"></i>
                    <span class="w-full flex items-center rounded-full border border-stone-200 bg-stone-50 py-2 pl-10 pr-4 text-sm text-stone-400 text-left truncate whitespace-nowrap">Search</span>
                </div>
            </a>
        </div>
    </div>
</div>
