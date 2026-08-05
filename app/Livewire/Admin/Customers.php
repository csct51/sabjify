<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Customers')]
class Customers extends Component
{
    public function toggleActive(User $user): void
    {
        $user->update(['is_active' => ! $user->is_active]);
    }

    #[Computed]
    public function totalCustomers(): int
    {
        return User::count();
    }

    public function render(): View
    {
        $users = User::withCount('orders')
            ->latest()
            ->get();

        return view('livewire.admin.customers', ['users' => $users]);
    }
}
