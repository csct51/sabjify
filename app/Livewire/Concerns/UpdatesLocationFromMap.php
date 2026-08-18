<?php

namespace App\Livewire\Concerns;

trait UpdatesLocationFromMap
{
    public function updateLocation(float $latitude, float $longitude, ?float $radiusKm = null): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        if ($radiusKm !== null && property_exists($this, 'radiusKm')) {
            $this->radiusKm = $radiusKm;
        }
    }

    public function updateRadius(float $radiusKm): void
    {
        if (property_exists($this, 'radiusKm')) {
            $this->radiusKm = $radiusKm;
        }
    }
}
