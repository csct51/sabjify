<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class StoreHeader extends Component
{
    #[On('address-updated')]
    public function refreshAddress(): void {}

    public function openAddressPrompt(): void
    {
        $this->dispatch('open-address-prompt');
    }

    public function render(): View
    {
        return view('livewire.store-header', [
            'defaultAddress' => Auth::guard('web')->user()?->addresses()->where('is_default', true)->first(),
        ]);
    }
}
