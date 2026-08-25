<?php

namespace App\Livewire;

use App\Livewire\Concerns\ChecksDeliveryArea;
use App\Models\Address;
use App\Services\GeocodingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class StoreHeader extends Component
{
    use ChecksDeliveryArea;

    public ?string $locationLabel = null;

    public ?bool $deliveryAvailable = null;

    public bool $hasLocation = false;

    public bool $needsDetection = false;

    #[On('address-updated')]
    public function refreshAddress(): void
    {
        $this->resolveLocation();
    }

    public function mount(): void
    {
        $this->resolveLocation();
    }

    protected function resolveLocation(): void
    {
        $user = Auth::guard('web')->user();
        $defaultAddress = $user?->addresses()->where('is_default', true)->first();

        if ($defaultAddress) {
            $this->locationLabel = $this->formatAddress($defaultAddress);
            $this->hasLocation = true;
            $this->needsDetection = false;

            if ($defaultAddress->latitude && $defaultAddress->longitude) {
                $this->deliveryAvailable = $this->checkDeliverable(
                    (float) $defaultAddress->latitude,
                    (float) $defaultAddress->longitude,
                );
            } else {
                $this->deliveryAvailable = null;
            }

            return;
        }

        $detected = Session::get('guest_delivery_location');

        if (is_array($detected)) {
            $this->locationLabel = $detected['label'] ?? 'Your location';
            $this->deliveryAvailable = (bool) ($detected['available'] ?? false);
            $this->hasLocation = true;
            $this->needsDetection = false;

            return;
        }

        $this->hasLocation = false;
        $this->needsDetection = true;
    }

    public function detectLocation(float $lat, float $lng): void
    {
        $address = app(GeocodingService::class)->reverse($lat, $lng);

        $label = 'Your location';

        if ($address && $address['address_line'] !== '') {
            $label = $address['address_line'];
        }

        $available = $this->checkDeliverable($lat, $lng);

        Session::put('guest_delivery_location', [
            'lat' => $lat,
            'lng' => $lng,
            'label' => $label,
            'available' => $available,
        ]);

        $this->locationLabel = $label;
        $this->deliveryAvailable = $available;
        $this->hasLocation = true;
        $this->needsDetection = false;
    }

    public function changeLocation(): void
    {
        Session::forget('guest_delivery_location');

        $this->locationLabel = null;
        $this->deliveryAvailable = null;
        $this->hasLocation = false;
        $this->needsDetection = true;
    }

    public function openAddressPrompt(): void
    {
        $this->dispatch('open-address-prompt');
    }

    protected function formatAddress(Address $address): string
    {
        return implode(', ', array_filter([
            $address->address_line,
            $address->landmark ?: null,
        ]));
    }

    public function render(): View
    {
        return view('livewire.store-header');
    }
}
