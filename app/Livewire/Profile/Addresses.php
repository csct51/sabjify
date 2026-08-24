<?php

namespace App\Livewire\Profile;

use App\Livewire\Concerns\AutofillsAddressFromLocation;
use App\Livewire\Concerns\ChecksDeliveryArea;
use App\Livewire\Concerns\UpdatesLocationFromMap;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Saved Addresses')]
class Addresses extends Component
{
    use AutofillsAddressFromLocation;
    use ChecksDeliveryArea;
    use UpdatesLocationFromMap;

    public string $addressMode = 'list';

    public ?int $editingAddressId = null;

    public string $label = 'Home';

    public string $receiverName = '';

    public string $receiverPhone = '';

    public string $addressLine = '';

    public string $landmark = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public bool $isDefault = false;

    public function mount(): void
    {
        $user = auth('web')->user();

        $this->receiverName = $user->name;
        $this->receiverPhone = $user->phone;
    }

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        return auth('web')->user()->addresses()->latest()->get();
    }

    public function openAddressForm(): void
    {
        $this->addressMode = 'form';
        $this->editingAddressId = null;

        $user = auth('web')->user();

        $this->label = 'Home';
        $this->receiverName = $user->name;
        $this->receiverPhone = $user->phone;
        $this->addressLine = '';
        $this->landmark = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->isDefault = ! $this->addresses()->contains('is_default', true);
    }

    public function editAddress(Address $address): void
    {
        abort_unless($address->user_id === auth('web')->id(), 403);

        $this->addressMode = 'form';
        $this->editingAddressId = $address->id;
        $this->label = $address->label;
        $this->receiverName = $address->receiver_name;
        $this->receiverPhone = $address->receiver_phone;
        $this->addressLine = $address->address_line;
        $this->landmark = $address->landmark ?? '';
        $this->latitude = $address->latitude;
        $this->longitude = $address->longitude;
        $this->isDefault = $address->is_default;
    }

    public function cancelAddressForm(): void
    {
        $this->addressMode = 'list';
        $this->editingAddressId = null;
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

        $data = [
            'label' => $validated['label'],
            'receiver_name' => $validated['receiverName'],
            'receiver_phone' => $validated['receiverPhone'],
            'address_line' => $validated['addressLine'],
            'landmark' => $validated['landmark'] ?: null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ];

        if ($this->editingAddressId) {
            $address = auth('web')->user()->addresses()->findOrFail($this->editingAddressId);

            $address->update([...$data, 'is_default' => $this->isDefault]);

            if ($this->isDefault) {
                $this->makeDefault($address);
            }
        } else {
            $address = auth('web')->user()->addresses()->create([
                ...$data,
                'city' => 'Raipur',
                'state' => 'Chhattisgarh',
                'pincode' => 0,
                'is_default' => $this->isDefault,
            ]);

            if ($this->isDefault) {
                $this->makeDefault($address);
            }
        }

        $this->addressMode = 'list';
        $this->editingAddressId = null;
        $this->dispatch('address-updated');
        $this->dispatch('toast', message: $this->isDefault ? 'Address saved and set as default.' : 'Address saved.');
    }

    public function setDefaultAddress(Address $address): void
    {
        abort_unless($address->user_id === auth('web')->id(), 403);

        $this->makeDefault($address);

        $this->dispatch('address-updated');
        $this->dispatch('toast', message: 'Default address updated.');
    }

    public function deleteAddress(Address $address): void
    {
        abort_unless($address->user_id === auth('web')->id(), 403);

        $address->delete();

        if (auth('web')->user()->addresses()->where('is_default', true)->doesntExist()) {
            auth('web')->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        $this->dispatch('address-updated');
        $this->dispatch('toast', message: 'Address deleted.');
    }

    public function render(): View
    {
        return view('livewire.profile.addresses');
    }

    private function makeDefault(Address $address): void
    {
        auth('web')->user()->addresses()
            ->whereKeyNot($address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }
}
