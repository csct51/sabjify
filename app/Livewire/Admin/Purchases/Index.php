<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Purchases')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->loadMissing('items');
            foreach ($purchase->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                $decrementQty = (float) $item->base_qty > 0 ? (float) $item->base_qty : (float) $item->qty;
                // Revert the purchase but never below zero.
                $product?->update(['current_stock' => max(0, round((float) $product->current_stock - $decrementQty, 3))]);
            }
            $purchase->delete();
        });

        $this->dispatch('toast', message: 'Purchase deleted.');
    }

    public function render(): View
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'items.product'])
            ->withCount('items')
            ->withSum('items as total', 'line_total')
            ->when($this->search !== '', fn ($q) => $q->where('purchase_number', 'like', '%'.$this->search.'%')
                ->orWhere('supplier_name', 'like', '%'.$this->search.'%')
                ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', '%'.$this->search.'%')))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.admin.purchases.index', [
            'purchases' => $purchases,
        ]);
    }
}
