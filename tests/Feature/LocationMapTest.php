<?php

use App\Models\DeliveryLocation;
use Illuminate\Support\Facades\Blade;

test('location map embeds its config as a parseable json attribute', function () {
    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" :radius-prop="\'radiusKm\'" :radius-km="10" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    expect($matches)->toHaveCount(2);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['lat'])->toBe(19.076)
        ->and($config['lng'])->toBe(72.8777)
        ->and($config['radius'])->toBe(10)
        ->and($config['pin'])->toBeTrue();
});

test('location map config hides the pin when no location is provided', function () {
    $html = Blade::render('<x-location-map :radius-prop="\'radiusKm\'" :radius-km="10" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['pin'])->toBeFalse()
        ->and($config['lat'])->toBe((float) config('mart.map_default_lat'))
        ->and($config['lng'])->toBe((float) config('mart.map_default_lng'))
        ->and($config['zoom'])->toBe(11);
});

test('location map config sets a null radius when no radius prop is provided', function () {
    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config)->toHaveKey('lat')
        ->and($config['radius'])->toBeNull();
});

test('location map config defaults the radius to zero on create', function () {
    $html = Blade::render('<x-location-map :radius-prop="\'radiusKm\'" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['radius'])->toBe(0)
        ->and($html)->toContain('min="0"')
        ->and($html)->toContain('value="0"');
});

test('location map config tells app.js whether it should autofill', function () {
    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['autofill'])->toBeFalse();

    $autofill = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" :autofill="true" />');

    preg_match('/data-config="([^"]*)"/', $autofill, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['autofill'])->toBeTrue();
});

test('location map config embeds active delivery areas for autofill maps only', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 19.076,
        'longitude' => 72.8777,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    DeliveryLocation::factory()->create(['is_active' => false]);

    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" :autofill="true" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['deliveryAreas'])->toHaveCount(1)
        ->and($config['deliveryAreas'][0])->toMatchArray([
            'lat' => 19.076,
            'lng' => 72.8777,
            'radiusKm' => 5,
        ]);

    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" />');

    preg_match('/data-config="([^"]*)"/', $html, $matches);

    $config = json_decode(html_entity_decode($matches[1]), true);

    expect($config['deliveryAreas'])->toBe([]);
});

test('location map does not rely on alpine wire magic to sync coordinates', function () {
    $html = Blade::render('<x-location-map :lat="19.076" :lng="72.8777" :radius-prop="\'radiusKm\'" :radius-km="10" />');

    expect($html)
        ->not->toContain('$wire')
        ->toContain('radius:update')
        ->toContain('value="10"');
});
