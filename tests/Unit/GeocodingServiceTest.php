<?php

use App\Services\GeocodingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('reverse geocodes a full nominatim address', function () {
    Http::fake([
        '*nominatim.openstreetmap.org/reverse*' => Http::response([
            'address' => [
                'house_number' => '42',
                'road' => 'MG Road',
                'suburb' => 'Bandra West',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postcode' => '400050',
            ],
        ]),
    ]);

    $address = app(GeocodingService::class)->reverse(19.0596, 72.8295);

    expect($address)->toBe([
        'address_line' => '42 MG Road',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400050',
    ]);
});

test('reverse geocode falls back to suburb when no road exists', function () {
    Http::fake([
        '*nominatim.openstreetmap.org/reverse*' => Http::response([
            'address' => [
                'suburb' => 'Koramangala',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postcode' => '560034',
            ],
        ]),
    ]);

    $address = app(GeocodingService::class)->reverse(12.9352, 77.6245);

    expect($address['address_line'])->toBe('Koramangala');
});

test('reverse geocode returns null on a failed response', function () {
    Http::fake([
        '*nominatim.openstreetmap.org/reverse*' => Http::response([], 500),
    ]);

    expect(app(GeocodingService::class)->reverse(19.0596, 72.8295))->toBeNull();
});

test('reverse geocode returns null on a connection failure', function () {
    Http::fake([
        '*nominatim.openstreetmap.org/reverse*' => Http::failedConnection(),
    ]);

    expect(app(GeocodingService::class)->reverse(19.0596, 72.8295))->toBeNull();
});

test('reverse geocode sends the app name as the user agent', function () {
    Http::fake([
        '*nominatim.openstreetmap.org/reverse*' => Http::response(['address' => ['city' => 'Mumbai']]),
    ]);

    app(GeocodingService::class)->reverse(19.0596, 72.8295);

    Http::assertSent(function (Request $request) {
        return str_contains($request->header('User-Agent')[0] ?? '', config('app.name'));
    });
});
