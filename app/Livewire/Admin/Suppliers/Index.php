<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Suppliers')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();

        $this->dispatch('toast', message: 'Supplier deleted.');
    }

    public function render(): View
    {
        $suppliers = Supplier::query()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('contact', 'like', '%'.$this->search.'%')
                ->orWhere('address', 'like', '%'.$this->search.'%'))
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.admin.suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
