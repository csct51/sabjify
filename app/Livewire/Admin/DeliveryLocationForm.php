<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UpdatesLocationFromMap;
use App\Models\DeliveryLocation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Delivery Location')]
class DeliveryLocationForm extends Component
{
    use UpdatesLocationFromMap;

    public ?DeliveryLocation $location = null;

    public string $name = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?float $radiusKm = null;

    public string $is_active = '1';

    public int $sort_order = 0;

    public function mount(?DeliveryLocation $deliveryLocation = null): void
    {
        $this->location = $deliveryLocation;

        if ($deliveryLocation) {
            $this->name = $deliveryLocation->name;
            $this->latitude = $deliveryLocation->latitude;
            $this->longitude = $deliveryLocation->longitude;
            $this->radiusKm = $deliveryLocation->radius_km;
            $this->is_active = $deliveryLocation->is_active ? '1' : '0';
            $this->sort_order = $deliveryLocation->sort_order;

            return;
        }

        $this->radiusKm = 1;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radiusKm' => ['required', 'numeric', 'gt:0'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data = [
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_km' => $this->radiusKm,
            'is_active' => $this->is_active === '1',
            'sort_order' => $this->sort_order,
        ];

        if ($this->location) {
            $this->location->update($data);
            session()->flash('success', 'Delivery location updated.');
        } else {
            DeliveryLocation::create($data);
            session()->flash('success', 'Delivery location created.');
        }

        $this->redirect(route('admin.delivery-locations.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.delivery-location-form');
    }
}
