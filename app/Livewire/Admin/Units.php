<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Units')]
class Units extends Component
{
    public string $newUnit = '';

    /**
     * @return Collection<int, Unit>
     */
    public function getUnitsProperty(): Collection
    {
        return Unit::withCount('products')->ordered()->get();
    }

    public function addUnit(): void
    {
        $this->validate(['newUnit' => ['required', 'string', 'max:20']]);

        $unit = trim($this->newUnit);

        if (Unit::query()->where('name', $unit)->exists()) {
            $this->addError('newUnit', "Unit \"{$unit}\" already exists.");

            return;
        }

        Unit::create([
            'name' => $unit,
            'sort_order' => Unit::max('sort_order') + 1,
        ]);

        $this->newUnit = '';

        $this->dispatch('toast', message: "Unit \"{$unit}\" added.");
    }

    public function removeUnit(Unit $unit): void
    {
        $inUse = Product::query()->where('unit', $unit->name)->exists();

        if ($inUse) {
            $this->addError('remove', "Cannot remove \"{$unit->name}\" because it is used by one or more products.");
            $this->dispatch('toast', message: "Cannot remove \"{$unit->name}\" because it is used by products.", type: 'error');

            return;
        }

        $unit->delete();

        $this->dispatch('toast', message: "Unit \"{$unit->name}\" removed.");
    }

    public function render(): View
    {
        return view('livewire.admin.units');
    }
}
