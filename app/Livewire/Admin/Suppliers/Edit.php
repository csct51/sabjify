<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Edit Supplier')]
class Edit extends Component
{
    public Supplier $supplier;

    public string $name = '';

    public string $contact = '';

    public string $address = '';

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
        $this->name = $supplier->name;
        $this->contact = $supplier->contact;
        $this->address = $supplier->address;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'contact' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        $this->supplier->update([
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
        ]);

        session()->flash('success', 'Supplier updated.');

        $this->redirect(route('admin.suppliers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.edit');
    }
}
