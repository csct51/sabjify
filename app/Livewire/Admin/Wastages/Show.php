<?php

namespace App\Livewire\Admin\Wastages;

use App\Models\Wastage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Wastage Details')]
class Show extends Component
{
    public Wastage $wastage;

    public function mount(Wastage $wastage): void
    {
        $this->wastage = $wastage->loadMissing(['items.product']);
    }

    public function render(): View
    {
        return view('livewire.admin.wastages.show');
    }
}
