<?php

namespace App\Livewire\Admin;

use App\Models\DeliveryLocation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Delivery Locations')]
class DeliveryLocations extends Component
{
    public function toggleActive(DeliveryLocation $location): void
    {
        $location->update(['is_active' => ! $location->is_active]);

        $this->dispatch('toast', message: $location->is_active
            ? "Delivery location \"{$location->name}\" is now active."
            : "Delivery location \"{$location->name}\" is now disabled.");
    }

    public function delete(DeliveryLocation $location): void
    {
        $location->delete();

        $this->dispatch('toast', message: "Delivery location \"{$location->name}\" deleted.");
    }

    public function render(): View
    {
        $locations = DeliveryLocation::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.delivery-locations', ['locations' => $locations]);
    }
}
