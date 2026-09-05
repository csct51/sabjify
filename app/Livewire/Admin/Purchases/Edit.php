<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Edit Purchase')]
class Edit extends Component
{
    public $purchase = null;

    /** @var array<int, array{product_id: ?int, unit: string, rate: string, qty: string}> */
    public array $rows = [];

    public string $supplier = 'Cash';

    public string $purchaseDate = '';

    public string $remark = '';

    public string $purchaseNumber = '';

    public string $productSearch = '';

    public ?int $formProductId = null;

    public string $formUnit = 'kg';

    public string $formRate = '';

    public string $formQty = '';

    public string $formError = '';

    public function mount($purchase = null): void
    {
        if ($purchase !== null && ! $purchase instanceof Purchase) {
            $purchase = Purchase::with('items')->find($purchase);
        }

        $this->purchase = $purchase;

        if ($purchase instanceof Purchase) {
            $this->supplier = $purchase->supplier_name ?? $purchase->supplier?->name ?? 'Cash';
            $this->purchaseNumber = $purchase->purchase_number ?? 'PUR-001';
            $this->purchaseDate = $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->remark = $purchase->remark ?? '';
            $purchase->loadMissing('items');
            $this->rows = $purchase->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'unit' => $item->unit ?? 'kg',
                'rate' => (string) ($item->rate ?? ''),
                'qty' => (string) ($item->qty ?? ''),
            ])->toArray();
        } else {
            $this->purchaseNumber = 'PUR-001';
            $this->purchaseDate = now()->format('Y-m-d');
        }

        if (empty($this->rows)) {
            $this->rows[] = ['product_id' => null, 'unit' => 'kg', 'rate' => '', 'qty' => ''];
        }
    }

    #[Computed]
    public function suppliers(): Collection
    {
        return Supplier::orderBy('name')->get();
    }

    #[Computed]
    public function selectedProduct(): ?Product
    {
        return $this->formProductId ? Product::find($this->formProductId) : null;
    }

    /**
     * @return array<int, string>
     */
    private function qtyRulesForUnit(string $unit): array
    {
        return Unit::integerOnlyFor($unit)
            ? ['required', 'integer', 'min:1']
            : ['required', 'numeric', 'min:0.001', 'max:999999'];
    }

    public function addProduct(): void
    {
        $this->formError = '';

        try {
            $this->validate([
                'formProductId' => ['required', 'integer', 'exists:products,id'],
                'formRate' => ['required', 'integer', 'min:1'],
            ], [
                'formProductId.required' => 'Please select a product.',
                'formRate.required' => 'Rate is required.',
                'formRate.integer' => 'Rate must be a whole number.',
                'formRate.min' => 'Rate must be at least 1.',
            ]);

            $product = Product::find($this->formProductId);
            $unit = $product ? $product->purchaseUnit() : 'kg';

            $this->validate([
                'formQty' => $this->qtyRulesForUnit($unit),
            ], [
                'formQty.required' => 'Qty is required.',
                'formQty.integer' => 'Qty must be a whole number.',
                'formQty.numeric' => 'Qty must be a number (e.g. 0.5 for 500g).',
                'formQty.min' => Unit::integerOnlyFor($unit) ? 'Qty must be at least 1.' : 'Qty must be at least 0.001.',
            ]);
        } catch (ValidationException $e) {
            $this->formError = 'Please complete the product details above.';
            throw $e;
        }

        $this->formUnit = $unit;

        $this->rows[] = [
            'product_id' => $this->formProductId,
            'unit' => $unit,
            'rate' => $this->formRate,
            'qty' => $this->formQty,
        ];

        $this->formProductId = null;
        $this->formRate = '';
        $this->formQty = '';
        $this->formError = '';
        $this->resetValidation();
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function delete(): void
    {
        if (! $this->purchase) {
            return;
        }

        DB::transaction(function () {
            /** @var Purchase $purchase */
            $purchase = $this->purchase;
            $purchase->loadMissing('items');
            foreach ($purchase->items as $oldItem) {
                $product = Product::whereKey($oldItem->product_id)->lockForUpdate()->first();
                $revertQty = (float) $oldItem->base_qty > 0 ? (float) $oldItem->base_qty : (float) $oldItem->qty;
                // Revert the purchase but never below zero.
                $product?->update(['current_stock' => max(0, round((float) $product->current_stock - $revertQty, 3))]);
            }
            $purchase->delete();
        });

        session()->flash('success', 'Purchase deleted.');

        $this->redirect(route('admin.purchases.index'), navigate: true);
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->with('category')
            ->when($this->productSearch !== '', function ($query) {
                $query->where('name', 'like', '%'.$this->productSearch.'%')
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$this->productSearch.'%'));
            })
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'rows.*.unit' => ['required', Rule::in(Unit::purchaseUnitOptions())],
            'rows.*.rate' => ['required', 'integer', 'min:1'],
            'rows.*.qty' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'supplier' => ['required', 'string', 'max:100'],
            'purchaseDate' => ['required', 'date'],
            'remark' => ['nullable', 'string', 'max:500'],
            'purchaseNumber' => ['nullable', 'string', 'max:20'],
        ]);

        foreach ($this->rows as $row) {
            $qtyFloat = (float) $row['qty'];

            if (Unit::integerOnlyFor($row['unit'] ?? '') && floor($qtyFloat) != $qtyFloat) {
                throw ValidationException::withMessages(['rows' => 'Qty for piece must be a whole number.']);
            }
        }

        DB::transaction(function () {
            /** @var Purchase $purchase */
            $purchase = $this->purchase;
            $supplierModel = Supplier::where('name', $this->supplier)->first();
            $total = collect($this->rows)->sum(fn ($r) => (int) round((float) $r['rate'] * (float) $r['qty']));

            // Revert old stock
            $purchase->loadMissing('items');
            foreach ($purchase->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $decrementQty = (float) $oldItem->base_qty > 0 ? (float) $oldItem->base_qty : (float) $oldItem->qty;
                if ($product && (float) $product->current_stock < $decrementQty - 1e-9) {
                    throw new ValidationException(
                        validator: Validator::make([], []),
                        response: response()->json(['message' => 'Insufficient stock to revert purchase for '.$product->name], 422)
                    );
                }
                $product?->decrement('current_stock', $decrementQty);
            }
            $purchase->items()->delete();

            $purchase->update([
                'supplier_id' => $supplierModel?->id,
                'supplier_name' => $this->supplier,
                'purchase_date' => $this->purchaseDate,
                'remark' => $this->remark ?: null,
                'total_amount' => $total,
            ]);

            foreach ($this->rows as $row) {
                $product = Product::find($row['product_id']);
                $qtyFloat = (float) $row['qty'];
                $lineTotal = (int) round((float) $row['rate'] * $qtyFloat);
                $baseQty = Unit::toBaseQty($row['unit'], $qtyFloat);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $row['product_id'],
                    'unit' => $row['unit'],
                    'rate' => (int) $row['rate'],
                    'qty' => $qtyFloat,
                    'line_total' => $lineTotal,
                    'base_qty' => $baseQty,
                ]);

                $product?->increment('current_stock', $baseQty);
            }
        });

        session()->flash('success', 'Purchase '.$this->purchaseNumber.' updated.');

        $this->redirect(route('admin.purchases.index'), navigate: true);
    }

    private function calculateBaseQty(int $qty, string $fromUnit, ?string $toUnit): int
    {
        if ($toUnit === null || $fromUnit === $toUnit) {
            return $qty;
        }

        $from = Unit::where('name', $fromUnit)->first();
        $to = Unit::where('name', $toUnit)->first();

        if (! $from || ! $to || $from->base_unit !== $to->base_unit) {
            return $qty;
        }

        $baseQty = $from->toBase((float) $qty);

        return (int) ceil($to->fromBase($baseQty));
    }

    public function render(): View
    {
        return view('livewire.admin.purchases.edit');
    }
}
