<?php

namespace App\Livewire\Concerns;

use App\Models\DeliveryLocation;
use App\Support\Geo;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

trait ChecksDeliveryArea
{
    /**
     * @return Collection<int, DeliveryLocation>
     */
    #[Computed]
    public function deliveryLocations(): Collection
    {
        return DeliveryLocation::active()
            ->orderBy('sort_order')
            ->get();
    }

    public function checkDeliverable(float $latitude, float $longitude): bool
    {
        if ($this->deliveryLocations()->isEmpty()) {
            return true;
        }

        return $this->deliveryLocations()->contains(
            fn (DeliveryLocation $location): bool => Geo::distanceKm($latitude, $longitude, $location->latitude, $location->longitude) <= $location->radius_km,
        );
    }
}
