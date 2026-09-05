<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-semibold text-stone-900">Selling Report</h1>
            <p class="text-sm text-stone-500 mt-0.5">Products sold from non-cancelled orders. Baskets excluded.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="preset('today')" class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Today</button>
            <button type="button" wire:click="preset('week')" class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">7 Days</button>
            <button type="button" wire:click="preset('month')" class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">30 Days</button>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-stone-200 p-4">
            <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Revenue</p>
            <p class="mt-1 text-2xl font-bold text-stone-900">{{ \Illuminate\Support\Number::currency($this->summary['revenue'], 'INR') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-4">
            <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Orders</p>
            <p class="mt-1 text-2xl font-bold text-stone-900">{{ $this->summary['orders'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-4">
            <p class="text-xs font-medium text-stone-400 uppercase tracking-wide">Qty Sold</p>
            <p class="mt-1 text-2xl font-bold text-stone-900">{{ rtrim(rtrim(number_format($this->summary['qty'], 3, '.', ''), '0'), '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="p-4 border-b border-stone-100 flex flex-wrap items-center gap-3">
            <input type="date" wire:model.live="dateFrom" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white" />
            <span class="text-sm text-stone-400">to</span>
            <input type="date" wire:model.live="dateTo" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white" />
            <select wire:model.live="category" class="rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-white">
                <option value="">All Categories</option>
                @foreach ($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="relative flex-1 max-w-sm ml-auto">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product..." class="w-full rounded-xl border border-stone-300 pl-9 pr-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Product</th>
                        <th class="px-4 py-3 text-left font-medium">Category</th>
                        <th class="px-4 py-3 text-right font-medium">Qty</th>
                        <th class="px-4 py-3 text-right font-medium">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $index => $row)
                        <tr class="hover:bg-stone-50" wire:key="selling-{{ $row['product_id'] }}">
                            <td class="px-4 py-3 text-stone-400">{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-stone-900">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $row['category'] }}</td>
                            @php $rowPacks = \App\Models\Unit::integerOnlyFor($row['unit']); @endphp
                            <td class="px-4 py-3 text-right text-stone-700">{{ rtrim(rtrim(number_format($row['qty'], 3, '.', ''), '0'), '.') }} @if ($rowPacks)<span class="text-stone-400">× {{ $row['unit'] }}</span>@else<span class="text-stone-400">{{ $row['unit'] }}</span>@endif</td>
                            <td class="px-4 py-3 text-right font-medium text-stone-900">{{ \Illuminate\Support\Number::currency($row['revenue'], 'INR') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400"><i data-lucide="clipboard-list" class="w-6 h-6"></i></span>
                                <p class="mt-3 font-medium text-stone-700">No sales in this period</p>
                                <p class="text-xs text-stone-500 mt-1">Try a different date range or filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-stone-100 flex items-center justify-between">
                <p class="text-xs text-stone-400">Page {{ $products->currentPage() }} of {{ $products->lastPage() }} · {{ $products->total() }} products</p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="previousPage" @disabled($products->onFirstPage()) class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium disabled:opacity-40">Previous</button>
                    <button type="button" wire:click="nextPage" @disabled(! $products->hasMorePages()) class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium disabled:opacity-40">Next</button>
                </div>
            </div>
        @endif
    </div>
</div>
