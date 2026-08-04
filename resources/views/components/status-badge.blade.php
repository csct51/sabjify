@php
    $colors = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'packing' => 'bg-violet-50 text-violet-700 border-violet-200',
        'out_for_delivery' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
        'delivered' => 'bg-green-50 text-green-700 border-green-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
    ];
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $colors[$status ?? ''] ?? 'bg-stone-50 text-stone-600 border-stone-200' }}">
    {{ \App\Models\Order::STATUSES[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>
