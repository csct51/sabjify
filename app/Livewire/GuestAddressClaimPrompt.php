<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestAddressClaimPrompt extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $this->refreshState();
    }

    #[On('address-updated')]
    public function refreshState(): void
    {
        $user = auth('web')->user();

        if (! $user) {
            $this->show = false;

            return;
        }

        $guest = session('guest_delivery_location');

        if (! is_array($guest) || empty($guest['address_line'])) {
            $this->show = false;

            return;
        }

        // Don't prompt if user already has this address saved
        $exists = $user->addresses()
            ->where('address_line', $guest['address_line'])
            ->where('latitude', $guest['latitude'] ?? null)
            ->where('longitude', $guest['longitude'] ?? null)
            ->exists();

        $this->show = ! $exists;
    }

    public function claim(): void
    {
        $user = auth('web')->user();

        if (! $user) {
            return;
        }

        $guest = session('guest_delivery_location');

        if (! is_array($guest) || empty($guest['address_line'])) {
            $this->show = false;

            return;
        }

        $exists = $user->addresses()
            ->where('address_line', $guest['address_line'])
            ->where('latitude', $guest['latitude'] ?? null)
            ->where('longitude', $guest['longitude'] ?? null)
            ->exists();

        if (! $exists) {
            $address = $user->addresses()->create([
                'label' => $guest['address_label'] ?? $guest['label'] ?? 'Home',
                'receiver_name' => $guest['receiver_name'] ?? $user->name,
                'receiver_phone' => $guest['receiver_phone'] ?? $user->phone,
                'address_line' => $guest['address_line'],
                'landmark' => $guest['landmark'] ?? null,
                'city' => $guest['city'] ?? 'Raipur',
                'state' => $guest['state'] ?? 'Chhattisgarh',
                'pincode' => $guest['pincode'] ?? 0,
                'latitude' => $guest['latitude'] ?? $guest['lat'] ?? null,
                'longitude' => $guest['longitude'] ?? $guest['lng'] ?? null,
                'is_default' => ! $user->addresses()->exists() || ! $user->addresses()->where('is_default', true)->exists(),
            ]);

            if ($address->is_default) {
                $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }
        }

        session()->forget('guest_delivery_location');

        $this->show = false;

        $this->dispatch('address-updated');

        $this->dispatch('toast', message: 'Address saved to your account.');
    }

    public function dismiss(): void
    {
        session()->forget('guest_delivery_location');

        $this->show = false;
    }

    public function render(): View
    {
        $guest = session('guest_delivery_location');

        return view('livewire.guest-address-claim-prompt', [
            'guest' => $guest,
        ]);
    }
}
