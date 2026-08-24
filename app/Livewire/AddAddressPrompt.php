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

    public string $city = '';

    public string $state = '';

    public string $pincode = '';

    public ?float $latitude = null;

    public ?float $longitude = null;

    public function mount(): void
    {
        if (! auth('web')->user()) {
            return;
        }
    }

    #[On('open-address-prompt')]
    public function openFromHeader(): void
    {
        $user = auth('web')->user();

        if (! $user) {
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
        return auth('web')->user()->addresses()->latest()->get();
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
        $this->city = '';
        $this->state = '';
        $this->pincode = '';
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
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'digits:6'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $address = auth('web')->user()->addresses()->create([
            'label' => $validated['label'],
            'receiver_name' => $validated['receiverName'],
            'receiver_phone' => $validated['receiverPhone'],
            'address_line' => $validated['addressLine'],
            'landmark' => $validated['landmark'] ?: null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_default' => true,
        ]);

        $this->makeDefault($address);

        $this->show = false;

        $this->dispatch('address-updated');

        $this->dispatch('toast', message: 'Address saved and set as default.');
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
