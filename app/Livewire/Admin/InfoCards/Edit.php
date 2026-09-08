<?php

namespace App\Livewire\Admin\InfoCards;

use App\Models\InfoCard;
use App\Support\InfoCards as InfoCardsHelper;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Edit Info Card')]
class Edit extends Component
{
    public int $position;

    public string $icon = '';

    public string $title = '';

    public string $subtitle = '';

    public function mount(int $position): void
    {
        abort_unless($position >= 1 && $position <= 6, 404);

        $this->position = $position;

        $card = InfoCardsHelper::all()[$position - 1];

        $this->icon = $card['icon'];
        $this->title = $card['title'];
        $this->subtitle = $card['subtitle'];
    }

    public function save(): void
    {
        $this->validate([
            'icon' => ['required', 'in:'.implode(',', InfoCardsHelper::ICONS)],
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['nullable', 'string', 'max:80'],
        ]);

        InfoCard::updateOrCreate(
            ['position' => $this->position],
            [
                'icon' => $this->icon,
                'title' => trim($this->title),
                'subtitle' => ($subtitle = trim($this->subtitle)) !== '' ? $subtitle : null,
            ]
        );

        session()->flash('success', 'Info card updated.');

        $this->redirect(route('admin.info-cards.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.info-cards.edit');
    }
}
