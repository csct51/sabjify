<?php

namespace App\Livewire;

use App\Livewire\Concerns\AutofillsAddressFromLocation;
use App\Livewire\Concerns\ChecksDeliveryArea;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AddAddressPrompt extends Component
{
    use AutofillsAddressFromLocation;
    use ChecksDeliveryArea;

    public bool $show = false;

    public bool $dismissable = false;

    public string $view = 'select';

    public ?int $selectedAddressId = null;

    public string $label = 'Home';

    public string $receiverName = '';

    public string $receiverPhone = '';

    public string $addressLine = '';

    public string $landmark = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public function mount(): void
    {
        // No early return — guests are handled in openFromHeader with a session-backed form.
    }

    #[On('open-address-prompt')]
    public function openFromHeader(): void
    {
        $user = auth('web')->user();

        if (! $user) {
            $this->show = true;
            $this->dismissable = true;
            $this->view = 'guest';
            $this->resetValidation();

            $guest = session('guest_delivery_location');

            if (is_array($guest)) {
                $this->label = $guest['address_label'] ?? 'Home';
                $this->receiverName = $guest['receiver_name'] ?? '';
                $this->receiverPhone = $guest['receiver_phone'] ?? '';
                $this->addressLine = $guest['address_line'] ?? $guest['label'] ?? '';
                $this->landmark = $guest['landmark'] ?? '';
                $this->latitude = isset($guest['latitude']) ? (float) $guest['latitude'] : (isset($guest['lat']) ? (float) $guest['lat'] : null);
                $this->longitude = isset($guest['longitude']) ? (float) $guest['longitude'] : (isset($guest['lng']) ? (float) $guest['lng'] : null);
            } else {
                $this->label = 'Home';
                $this->receiverName = '';
                $this->receiverPhone = '';
                $this->addressLine = '';
                $this->landmark = '';
                $this->latitude = null;
                $this->longitude = null;
            }

            return;
        }

        $this->show = true;
        $this->dismissable = true;
        $this->view = 'select';
        $this->resetValidation();
        $this->receiverName = $user->name;
        $this->receiverPhone = $user->phone;
        $this->selectedAddressId = $user->addresses()->where('is_default', true)->value('id')
            ?? $user->addresses()->latest()->value('id');
    }

    public function dismiss(): void
    {
        $this->show = false;
        $this->dismissable = false;
        $this->resetValidation();
    }

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        $user = auth('web')->user();

        if (! $user) {
            return collect();
        }

        return $user->addresses()->latest()->get();
    }

    public function selectAddress(int $addressId): void
    {
        auth('web')->user()->addresses()->findOrFail($addressId);

        $this->selectedAddressId = $addressId;
        $this->resetValidation('selection');
    }

    public function confirmSelection(): void
    {
        $user = auth('web')->user();

        $this->resetValidation('selection');

        if ($this->selectedAddressId === null) {
            $this->addError('selection', 'Please select an address or add a new one.');

            return;
        }

        $address = $user->addresses()->findOrFail($this->selectedAddressId);

        $this->makeDefault($address);

        $this->show = false;

        $this->dispatch('address-updated');

        $this->dispatch('toast', message: 'Delivery address selected.');
    }

    public function openAddForm(): void
    {
        $user = auth('web')->user();

        $this->view = 'form';
        $this->resetValidation();
        $this->label = 'Home';
        $this->receiverName = $user->name;
        $this->receiverPhone = $user->phone;
        $this->addressLine = '';
        $this->landmark = '';
        $this->latitude = null;
        $this->longitude = null;
    }

    public function backToSelect(): void
    {
        $this->view = 'select';
        $this->resetValidation();
    }

    public function saveAddress(): void
    {
        $validated = $this->validate([
            'label' => ['required', 'string', 'max:20'],
            'receiverName' => ['required', 'string', 'max:100'],
            'receiverPhone' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'addressLine' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($this->deliveryLocations()->isNotEmpty()) {
            if ($validated['latitude'] === null || $validated['longitude'] === null) {
                $this->addError('delivery', 'Please tap inside the green circle to set your delivery location.');

                return;
            }

            if (! $this->checkDeliverable((float) $validated['latitude'], (float) $validated['longitude'])) {
                $this->addError('delivery', 'We don\'t deliver to this location yet. Please choose a location inside the green circle.');

                return;
            }
        }

        $address = auth('web')->user()->addresses()->create([
            'label' => $validated['label'],
            'receiver_name' => $validated['receiverName'],
            'receiver_phone' => $validated['receiverPhone'],
            'address_line' => $validated['addressLine'],
            'landmark' => $validated['landmark'] ?: null,
            'city' => 'Raipur',
            'state' => 'Chhattisgarh',
            'pincode' => 0,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_default' => true,
        ]);

        $this->makeDefault($address);

        $this->show = false;

        $this->dispatch('address-updated');

        $this->dispatch('toast', message: 'Address saved and set as default.');
    }

    public function saveGuestLocation(): void
    {
        $validated = $this->validate([
            'addressLine' => ['required', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($this->deliveryLocations()->isNotEmpty()) {
            if ($validated['latitude'] === null || $validated['longitude'] === null) {
                $this->addError('delivery', 'Please tap inside the green circle to set your delivery location.');

                return;
            }

            if (! $this->checkDeliverable((float) $validated['latitude'], (float) $validated['longitude'])) {
                $this->addError('delivery', 'We don\'t deliver to this location yet. Please choose a location inside the green circle.');

                return;
            }
        }

        $available = $this->deliveryLocations()->isEmpty()
            ? true
            : $this->checkDeliverable((float) $validated['latitude'], (float) $validated['longitude']);

        session()->put('guest_delivery_location', [
            'label' => $validated['addressLine'],
            'address_label' => 'Home',
            'receiver_name' => null,
            'receiver_phone' => null,
            'address_line' => $validated['addressLine'],
            'landmark' => $validated['landmark'] ?: null,
            'city' => 'Raipur',
            'state' => 'Chhattisgarh',
            'pincode' => 0,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'lat' => $validated['latitude'],
            'lng' => $validated['longitude'],
            'available' => $available,
        ]);

        $this->show = false;

        $this->dispatch('address-updated');

        $this->dispatch('toast', message: 'Delivery location set. Login to save it for faster checkout.');
    }

    public function render(): View
    {
        return view('livewire.add-address-prompt');
    }

    private function makeDefault(Address $address): void
    {
        auth('web')->user()->addresses()
            ->whereKeyNot($address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }
}
