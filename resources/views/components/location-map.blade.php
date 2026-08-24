@props([
    'latProp' => 'latitude',
    'lngProp' => 'longitude',
    'radiusProp' => null,
    'lat' => null,
    'lng' => null,
    'routes' => [],
    'radiusKm' => null,
    'height' => 'h-64',
    'autofill' => false,
    'readonly' => false,
    'geolocate' => false,
    'existingAreas' => false,
    'excludeAreaId' => null,
])

@php
    use App\Models\DeliveryLocation;

    $defaultLat = (float) config('mart.map_default_lat');
    $defaultLng = (float) config('mart.map_default_lng');
    $centerLat = $lat !== null ? (float) $lat : $defaultLat;
    $centerLng = $lng !== null ? (float) $lng : $defaultLng;
    $initialRadius = $radiusKm !== null ? (float) $radiusKm : 0.0;
    $deliveryAreas = $autofill && ! $readonly
        ? DeliveryLocation::active()->get()->map(fn (DeliveryLocation $location): array => [
            'lat' => (float) $location->latitude,
            'lng' => (float) $location->longitude,
            'radiusKm' => (float) $location->radius_km,
        ])->values()->all()
        : [];
    $existingAreas = $existingAreas && ! $readonly
        ? DeliveryLocation::active()
            ->when($excludeAreaId !== null, fn ($query) => $query->whereKeyNot($excludeAreaId))
            ->get()
            ->map(fn (DeliveryLocation $location): array => [
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
                'radiusKm' => (float) $location->radius_km,
                'name' => $location->name,
            ])->values()->all()
        : [];
    $routeItems = $readonly ? $routes : [];
    $mapConfig = [
        'lat' => $centerLat,
        'lng' => $centerLng,
        'zoom' => $lat !== null ? 15 : 11,
        'radius' => $radiusProp !== null ? $initialRadius : null,
        'pin' => $lat !== null && $lng !== null,
        'autofill' => $autofill && ! $readonly,
        'deliveryAreas' => $deliveryAreas,
        'existingAreas' => $existingAreas,
        'readonly' => $readonly,
        'routes' => $routeItems,
        'geolocate' => $geolocate && ! $readonly,
    ];
@endphp

<div x-data>
    @if ($geolocate && ! $readonly)
        <button
            type="button"
            x-on:click="$refs.map.dispatchEvent(new CustomEvent('locate:request'))"
            class="mb-2 inline-flex items-center gap-1.5 rounded-xl border border-brand-600 text-brand-600 hover:bg-brand-50 font-semibold px-3.5 py-2 text-sm transition"
        >
            <i data-lucide="crosshair" class="w-4 h-4"></i> Use my current location
        </button>
    @endif

    <div
        wire:ignore
        data-leaflet-map
        data-config="{{ json_encode($mapConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
        class="{{ $height }} w-full rounded-xl border border-stone-300 z-0"
        x-ref="map"
    ></div>

    @if ($radiusProp !== null)
        <div class="mt-3">
            <label class="block text-sm font-medium text-stone-700 mb-1">Delivery Radius (km) <span class="text-red-500">*</span></label>
            <input
                type="number"
                min="0"
                step="any"
                value="{{ $initialRadius }}"
                x-on:input.debounce.300ms="$refs.map.dispatchEvent(new CustomEvent('radius:update', { detail: { radiusKm: Number($el.value) } }))"
                class="w-full rounded-xl border border-stone-300 px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
            >
        </div>
    @endif

    @if (! $readonly)
        <p class="mt-1.5 text-xs text-stone-400">@if ($autofill) Drop the pin on your delivery location and the address fields will be filled in automatically. @else Click the map or drag the pin to set the center of this delivery zone. @endif</p>
    @endif
</div>