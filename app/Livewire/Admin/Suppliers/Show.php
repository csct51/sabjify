<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Supplier Details')]
class Show extends Component
{
    public Supplier $supplier;

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.show');
    }
}
