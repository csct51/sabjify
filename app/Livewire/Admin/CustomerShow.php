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

    public ?string $status = null;

    public function filter(string $status): void
    {
        $this->status = $status === 'all' ? null : $status;
    }

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
        return $this->user->orders()
            ->with('items')
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->get();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function counts(): array
    {
        $counts = $this->user->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'all' => $this->user->orders()->count(),
            ...$counts,
        ];
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
