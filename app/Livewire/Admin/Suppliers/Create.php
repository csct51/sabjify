<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Create Supplier')]
class Create extends Component
{
    public string $name = '';

    public string $contact = '';

    public string $address = '';

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'contact' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        Supplier::create([
            'name' => $this->name,
            'contact' => $this->contact,
            'address' => $this->address,
        ]);

        session()->flash('success', 'Supplier created.');

        $this->redirect(route('admin.suppliers.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.create');
    }
}
