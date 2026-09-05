<?php

namespace App\Livewire\Admin\Units;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Edit Unit')]
class Edit extends Component
{
    public Unit $unit;

    public string $name = '';

    public string $base_unit = 'g';

    public string $to_base_factor = '1';

    public int $sort_order = 0;

    public bool $is_base = false;

    public string $purchase_unit = '';

    public bool $integer_only = false;

    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
        $this->name = $unit->name;
        $this->base_unit = $unit->base_unit ?? 'g';
        $this->to_base_factor = (string) $unit->to_base_factor;
        $this->sort_order = $unit->sort_order;
        $this->is_base = (bool) $unit->is_base;
        $this->purchase_unit = $unit->purchase_unit ?? '';
        $this->integer_only = (bool) $unit->integer_only;
    }

    /**
     * @return Collection<int, Unit>
     */
    #[Computed]
    public function baseOptions(): Collection
    {
        return Unit::isBase()->ordered()->get();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:20', 'unique:units,name,'.$this->unit->id],
            'base_unit' => [$this->is_base ? 'nullable' : 'required', 'string', Rule::in(Unit::isBase()->pluck('name')->toArray())],
            'to_base_factor' => ['required', 'numeric', 'min:0.0001'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_base' => ['boolean'],
            'purchase_unit' => ['nullable', 'string', 'max:20'],
            'integer_only' => ['boolean'],
        ]);

        if ($this->unit->is_base && ! $this->is_base) {
            $this->assertBaseUnused();
        }

        // Renaming a base is delete+create: block rename while referenced.
        if ($this->unit->is_base && trim($this->name) !== $this->unit->name) {
            $this->assertBaseUnused();
        }

        $this->unit->update([
            'name' => trim($this->name),
            'base_unit' => $this->is_base ? null : $this->base_unit,
            'to_base_factor' => $this->to_base_factor,
            'sort_order' => $this->sort_order,
            'is_base' => $this->is_base,
            'purchase_unit' => $this->is_base ? ($this->purchase_unit !== '' ? $this->purchase_unit : trim($this->name)) : null,
            'integer_only' => $this->is_base && $this->integer_only,
        ]);

        session()->flash('success', 'Unit updated.');

        $this->redirect(route('admin.units.index'), navigate: true);
    }

    private function assertBaseUnused(): void
    {
        $name = $this->unit->name;
        $usedByUnits = Unit::where('base_unit', $name)->whereKeyNot($this->unit->id)->exists();
        $usedByProducts = Product::where('base_unit', $name)->exists();

        if ($usedByUnits || $usedByProducts) {
            throw ValidationException::withMessages([
                'is_base' => "Base unit \"{$name}\" is in use and cannot be unflagged or renamed. Move its products and units to another base first.",
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.units.edit');
    }
}
