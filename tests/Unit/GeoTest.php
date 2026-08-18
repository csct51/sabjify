<?php

use App\Support\Geo;

test('distance between identical coordinates is zero', function () {
    expect(Geo::distanceKm(19.076, 72.8777, 19.076, 72.8777))->toBe(0.0);
});

test('distance between delhi and mumbai is roughly 1150 km', function () {
    $distance = Geo::distanceKm(28.6139, 77.2090, 19.0760, 72.8777);

    expect($distance)->toBeGreaterThan(1100)
        ->and($distance)->toBeLessThan(1200);
});

test('distance between two nearby points is small', function () {
    $distance = Geo::distanceKm(19.0760, 72.8777, 19.0790, 72.8827);

    expect($distance)->toBeLessThan(1);
});
