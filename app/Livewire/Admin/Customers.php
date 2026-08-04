<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Customers')]
class Customers extends Component
{
    use WithPagination;

    public string $search = '';

    public string $role = 'customer';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(User $user): void
    {
        if ($user->isAdmin()) {
            $this->addError('toggle', 'Admin accounts cannot be deactivated.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    #[Computed]
    public function totalCustomers(): int
    {
        return User::where('role', 'customer')->count();
    }

    public function render(): View
    {
        $users = User::withCount('orders')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->role, fn ($query) => $query->where('role', $this->role))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.customers', ['users' => $users]);
    }
}
