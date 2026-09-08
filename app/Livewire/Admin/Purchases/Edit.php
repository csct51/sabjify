<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
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

    public function updatedFormProductId(?int $value): void
    {
        if ($value === null) {
            return;
        }

        $product = Product::find($value);

        $this->formRate = (string) ($product?->defaultUnit()?->price ?? $product?->price ?? '');
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
        $this->dispatch('product-added');
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
                $revertQty = Unit::storedBaseQty($oldItem->unit ?? '', (float) $oldItem->qty, (float) $oldItem->base_qty);
                // Revert the purchase but never below zero.
                $product?->update(['current_stock' => max(0, round((float) $product->current_stock - $revertQty, 3))]);
            }
            $purchase->delete();
        });

        session()->flash('success', 'Purchase deleted.');

        $this->redirect(route('admin.purchases.index'), navigate: true);
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

            // Old base per product (plausibility-guarded).
            $purchase->loadMissing('items');
            $oldBase = [];

            foreach ($purchase->items as $oldItem) {
                $oldQty = (float) $oldItem->base_qty > 0 ? (float) $oldItem->base_qty : (float) $oldItem->qty;

                if (! Unit::baseQtyPlausible($oldItem->unit ?? '', (float) $oldItem->qty, $oldQty)) {
                    throw ValidationException::withMessages([
                        'rows' => "Stored base quantity looks wrong for {$oldItem->product?->name} ({$oldItem->qty} {$oldItem->unit} recorded as {$oldQty}). Delete and re-create this entry instead of editing.",
                    ]);
                }

                $oldBase[$oldItem->product_id] = round(($oldBase[$oldItem->product_id] ?? 0) + $oldQty, 3);
            }

            // New base per product.
            $newBase = [];

            foreach ($this->rows as $row) {
                $qtyFloat = (float) $row['qty'];
                $baseQty = Unit::toBaseQty($row['unit'], $qtyFloat);
                $newBase[$row['product_id']] = round(($newBase[$row['product_id']] ?? 0) + $baseQty, 3);
            }

            // Net movement only: untouched products (and header-only edits)
            // move no stock at all.
            foreach (array_unique([...array_keys($oldBase), ...array_keys($newBase)]) as $productId) {
                $diff = round(($newBase[$productId] ?? 0) - ($oldBase[$productId] ?? 0), 3);

                if ($diff > 0) {
                    Product::find($productId)?->increment('current_stock', $diff);
                } elseif ($diff < 0) {
                    $product = Product::whereKey($productId)->lockForUpdate()->first();

                    if ($product && (float) $product->current_stock < -$diff - 1e-9) {
                        throw new ValidationException(
                            validator: Validator::make([], []),
                            response: response()->json(['message' => 'Insufficient stock to revert purchase for '.$product->name], 422)
                        );
                    }

                    $product?->decrement('current_stock', -$diff);
                }
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
            }
        });

        session()->flash('success', 'Purchase '.$this->purchaseNumber.' updated.');

        $this->redirect(route('admin.purchases.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.purchases.edit');
    }
}
