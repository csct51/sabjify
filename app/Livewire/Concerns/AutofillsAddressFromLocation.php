<?php

namespace App\Livewire\Concerns;

use App\Services\GeocodingService;

trait AutofillsAddressFromLocation
{
    public function reverseGeocode(float $latitude, float $longitude): bool
    {
        $address = app(GeocodingService::class)->reverse($latitude, $longitude);

        $this->latitude = $latitude;
        $this->longitude = $longitude;

        if ($address !== null) {
            $this->addressLine = $address['address_line'];
            $this->city = $address['city'];
            $this->state = $address['state'];
            $this->pincode = $address['pincode'];
        }

        return $this->checkDeliverable($latitude, $longitude);
    }
}
