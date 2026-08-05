<?php

namespace App\Livewire;

use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('My Profile')]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $addressMode = 'list';

    public ?int $editingAddressId = null;

    public string $label = 'Home';

    public string $receiverName = '';

    public string $receiverPhone = '';

    public string $addressLine = '';

    public string $landmark = '';

    public string $city = '';

    public string $state = '';

    public string $pincode = '';

    public bool $isDefault = false;

    public function mount(): void
    {
        $user = auth('web')->user();

        $this->name = $user->name;
        $this->email = $user->email ?? '';
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

    public function saveProfile(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth('web')->id())],
        ]);

        auth('web')->user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?: null,
        ]);

        $this->dispatch('toast', message: 'Profile updated successfully.');
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
        $this->city = '';
        $this->state = '';
        $this->pincode = '';
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
        $this->city = $address->city;
        $this->state = $address->state;
        $this->pincode = $address->pincode;
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
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'digits:6'],
        ]);

        $data = [
            'label' => $validated['label'],
            'receiver_name' => $validated['receiverName'],
            'receiver_phone' => $validated['receiverPhone'],
            'address_line' => $validated['addressLine'],
            'landmark' => $validated['landmark'] ?: null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'],
        ];

        if ($this->editingAddressId) {
            $address = auth('web')->user()->addresses()->findOrFail($this->editingAddressId);

            $address->update([...$data, 'is_default' => $this->isDefault]);

            if ($this->isDefault) {
                $this->makeDefault($address);
            }
        } else {
            $address = auth('web')->user()->addresses()->create([...$data, 'is_default' => $this->isDefault]);

            if ($this->isDefault) {
                $this->makeDefault($address);
            }
        }

        $this->addressMode = 'list';
        $this->editingAddressId = null;
        $this->dispatch('toast', message: $this->isDefault ? 'Address saved and set as default.' : 'Address saved.');
    }

    public function setDefaultAddress(Address $address): void
    {
        abort_unless($address->user_id === auth('web')->id(), 403);

        $this->makeDefault($address);

        $this->dispatch('toast', message: 'Default address updated.');
    }

    public function deleteAddress(Address $address): void
    {
        abort_unless($address->user_id === auth('web')->id(), 403);

        $address->delete();

        if (auth('web')->user()->addresses()->where('is_default', true)->doesntExist()) {
            auth('web')->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        $this->dispatch('toast', message: 'Address deleted.');
    }

    public function logout(): void
    {
        auth('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.profile');
    }

    private function makeDefault(Address $address): void
    {
        auth('web')->user()->addresses()
            ->whereKeyNot($address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);
    }
}
