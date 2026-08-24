<?php

namespace App\Livewire\Concerns;

trait AutofillsAddressFromLocation
{
    public function reverseGeocode(float $latitude, float $longitude): bool
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        return $this->checkDeliverable($latitude, $longitude);
    }
}
