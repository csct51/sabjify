<div>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-stone-400 mb-1">{{ count($cards) }} cards</p>
            <h2 class="text-lg font-semibold text-stone-900">Info Cards</h2>
            <p class="text-sm text-stone-500">The six highlight cards on the homepage, basket and product pages.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-400">
                    <tr>
                        <th class="w-10 px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Icon</th>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Subtitle</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($cards as $index => $card)
                        <tr wire:key="info-card-{{ $index }}" class="hover:bg-stone-50">
                            <td class="px-4 py-3 text-stone-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600"><i data-lucide="{{ $card['icon'] }}" class="w-4 h-4"></i></span>
                            </td>
                            <td class="px-4 py-3 font-medium text-stone-900">{{ $card['title'] }}</td>
                            <td class="px-4 py-3 text-stone-500">{{ $card['subtitle'] !== '' ? $card['subtitle'] : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.info-cards.edit', $index + 1) }}" wire:navigate class="rounded-lg border border-stone-200 hover:bg-stone-50 px-3 py-1.5 text-xs font-medium">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
