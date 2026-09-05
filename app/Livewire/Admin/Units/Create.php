<?php

namespace App\Livewire\Admin\Units;

use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Create Unit')]
class Create extends Component
{
    public string $name = '';

    public string $base_unit = 'g';

    public string $to_base_factor = '1';

    public int $sort_order = 0;

    public bool $is_base = false;

    public string $purchase_unit = '';

    public bool $integer_only = false;

    public function mount(): void
    {
        $this->sort_order = (int) (Unit::max('sort_order') ?? 0) + 1;
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
            'name' => ['required', 'string', 'max:20', 'unique:units,name'],
            'base_unit' => [$this->is_base ? 'nullable' : 'required', 'string', Rule::in(Unit::isBase()->pluck('name')->toArray())],
            'to_base_factor' => ['required', 'numeric', 'min:0.0001'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_base' => ['boolean'],
            'purchase_unit' => ['nullable', 'string', 'max:20'],
            'integer_only' => ['boolean'],
        ]);

        Unit::create([
            'name' => trim($this->name),
            'base_unit' => $this->is_base ? null : $this->base_unit,
            'to_base_factor' => $this->to_base_factor,
            'sort_order' => $this->sort_order,
            'is_base' => $this->is_base,
            'purchase_unit' => $this->is_base ? ($this->purchase_unit !== '' ? $this->purchase_unit : trim($this->name)) : null,
            'integer_only' => $this->is_base && $this->integer_only,
        ]);

        session()->flash('success', 'Unit created.');

        $this->redirect(route('admin.units.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.units.create');
    }
}
