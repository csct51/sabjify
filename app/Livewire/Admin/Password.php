<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Change Password')]
class Password extends Component
{
    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword' => ['required', 'string', 'same:newPasswordConfirmation', 'min:8'],
        ]);

        if (! Hash::check($this->currentPassword, Auth::guard('admin')->user()->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        Auth::guard('admin')->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');

        $this->dispatch('toast', message: 'Password updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.admin.password');
    }
}
