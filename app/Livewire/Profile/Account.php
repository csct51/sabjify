<?php

namespace App\Livewire\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.store')]
#[Title('Account Details')]
class Account extends Component
{
    public string $name = '';

    public string $email = '';

    public function mount(): void
    {
        $user = auth('web')->user();

        $this->name = $user->name;
        $this->email = $user->email ?? '';
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

    public function render(): View
    {
        return view('livewire.profile.account');
    }
}
