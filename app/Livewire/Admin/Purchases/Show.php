<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Purchase Details')]
class Show extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        $this->purchase = $purchase->loadMissing(['supplier', 'items.product']);
    }

    public function render(): View
    {
        return view('livewire.admin.purchases.show');
    }
}
