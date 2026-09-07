<?php

namespace App\Livewire\Admin\Wastages;

use App\Models\Product;
use App\Models\Wastage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Wastage')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Wastage $wastage): void
    {
        DB::transaction(function () use ($wastage) {
            $wastage->loadMissing('items');
            foreach ($wastage->items as $item) {
                $product = Product::find($item->product_id);
                $restoreQty = Unit::storedBaseQty($item->unit ?? '', (float) $item->qty, (float) $item->base_qty);
                $product?->increment('current_stock', $restoreQty);
            }
            $wastage->delete();
        });

        $this->dispatch('toast', message: 'Wastage deleted and stock restored.');
    }

    public function render(): View
    {
        $wastages = Wastage::query()
            ->with(['items.product'])
            ->withCount('items')
            ->withSum('items as total_qty', 'qty')
            ->when($this->search !== '', fn ($q) => $q->where('wastage_number', 'like', '%'.$this->search.'%')
                ->orWhere('reason', 'like', '%'.$this->search.'%')
                ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', '%'.$this->search.'%')))
            ->orderByDesc('wastage_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.admin.wastages.index', [
            'wastages' => $wastages,
        ]);
    }
}
