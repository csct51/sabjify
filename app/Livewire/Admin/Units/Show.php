<?php

namespace App\Livewire\Admin\Units;

use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Unit Details')]
class Show extends Component
{
    public Unit $unit;

    public function mount(Unit $unit): void
    {
        $this->unit = $unit->loadCount('products');
    }

    public function render(): View
    {
        return view('livewire.admin.units.show');
    }
}
