<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Customer Details')]
class CustomerShow extends Component
{
    #[Locked]
    public User $user;

    public function mount(): void
    {
        abort_unless(auth('admin')->check(), 403);

        $this->user->load('addresses');
    }

    public function toggleActive(): void
    {
        $this->user->update(['is_active' => ! $this->user->is_active]);
    }

    /**
     * @return Collection<int, Order>
     */
    #[Computed]
    public function orders(): Collection
    {
        return $this->user->orders()->with('items')->latest()->get();
    }

    #[Computed]
    public function totalSpent(): int
    {
        return (int) $this->user->orders()->sum('total');
    }

    #[Computed]
    public function activeOrders(): int
    {
        return $this->user->orders()
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->count();
    }

    public function render(): View
    {
        return view('livewire.admin.customer-show');
    }
}
