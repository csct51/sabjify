<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    /**
     * Reverse-geocode coordinates into address parts, or null when the lookup
     * fails or returns no usable address.
     *
     * @return array{address_line: string, city: string, state: string, pincode: string}|null
     */
    public function reverse(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => config('app.name')])
                ->acceptJson()
                ->get(config('mart.nominatim_base_url').'/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'addressdetails' => 1,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $address = $response->json('address');

        if (! is_array($address)) {
            return null;
        }

        return [
            'address_line' => $this->addressLine($address),
            'city' => $this->city($address),
            'state' => $this->state($address),
            'pincode' => $this->pincode($address),
        ];
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function addressLine(array $address): string
    {
        $house = (string) ($address['house_number'] ?? '');
        $road = (string) ($address['road'] ?? '');

        if ($house !== '' && $road !== '') {
            return trim($house.' '.$road);
        }

        if ($road !== '') {
            return $road;
        }

        foreach (['suburb', 'neighbourhood', 'quarter', 'pedestrian'] as $key) {
            if (isset($address[$key]) && (string) $address[$key] !== '') {
                return (string) $address[$key];
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function city(array $address): string
    {
        foreach (['city', 'town', 'village', 'municipality', 'suburb'] as $key) {
            if (isset($address[$key]) && (string) $address[$key] !== '') {
                return (string) $address[$key];
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function state(array $address): string
    {
        foreach (['state', 'state_district', 'region'] as $key) {
            if (isset($address[$key]) && (string) $address[$key] !== '') {
                return (string) $address[$key];
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function pincode(array $address): string
    {
        return (string) ($address['postcode'] ?? '');
    }
}
