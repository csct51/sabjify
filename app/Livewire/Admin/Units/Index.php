<?php

namespace App\Livewire\Admin\Units;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Units')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Unit $unit): void
    {
        if ($unit->is_base && (Unit::where('base_unit', $unit->name)->exists() || Product::where('base_unit', $unit->name)->exists())) {
            $this->addError('remove', 'Cannot delete base unit "'.$unit->name.'" because it is in use. Move its products and units to another base first.');

            return;
        }

        if (Product::where('unit', $unit->name)->exists()) {
            $this->addError('remove', 'Cannot delete unit "'.$unit->name.'" because it is used by products.');

            return;
        }

        if ($unit->products()->exists()) {
            $this->addError('remove', 'Cannot delete unit "'.$unit->name.'" because it is used by products.');

            return;
        }

        $unit->delete();

        $this->dispatch('toast', message: 'Unit deleted.');
    }

    public function render(): View
    {
        $units = Unit::withCount('products')
            ->ordered()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('base_unit', 'like', '%'.$this->search.'%'))
            ->paginate(15);

        return view('livewire.admin.units.index', [
            'units' => $units,
        ]);
    }
}
