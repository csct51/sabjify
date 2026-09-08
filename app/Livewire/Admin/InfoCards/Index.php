<?php

namespace App\Livewire\Admin\InfoCards;

use App\Support\InfoCards as InfoCardsHelper;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Info Cards')]
class Index extends Component
{
    public function render(): View
    {
        return view('livewire.admin.info-cards.index', [
            'cards' => InfoCardsHelper::all(),
        ]);
    }
}
