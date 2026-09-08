<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Create Purchase')]
class Create extends Component
{
    /** @var array<int, array{product_id: ?int, unit: string, rate: string, qty: string}> */
    public array $rows = [];

    public string $supplier = 'Cash';

    public string $purchaseDate = '';

    public string $remark = '';

    public string $purchaseNumber = '';

    public ?int $formProductId = null;

    public string $formRate = '';

    public string $formQty = '';

    public string $formError = '';

    public function mount(): void
    {
        $this->purchaseNumber = $this->generatePurchaseNumber();
        $this->purchaseDate = now()->format('Y-m-d');
    }

    private function generatePurchaseNumber(): string
    {
        $prefix = 'PUR-';
        $max = Purchase::query()
            ->where('purchase_number', 'like', $prefix.'%')
            ->pluck('purchase_number')
            ->map(fn (string $n) => ($s = substr($n, strlen($prefix))) !== '' && ctype_digit($s) ? (int) $s : null)
            ->filter()
            ->max() ?? 0;

        return $prefix.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    #[Computed]
    public function formUnit(): string
    {
        return $this->selectedProduct?->purchaseUnit() ?? 'kg';
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
            $supplierModel = Supplier::where('name', $this->supplier)->first();
            $total = collect($this->rows)->sum(fn ($r) => (int) round((float) $r['rate'] * (float) $r['qty']));

            $purchaseNumber = $this->generatePurchaseNumber();

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $supplierModel?->id,
                'supplier_name' => $this->supplier,
                'purchase_date' => $this->purchaseDate,
                'remark' => $this->remark ?: null,
                'total_amount' => $total,
                'created_by' => auth('admin')->id(),
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

        session()->flash('success', 'Purchase '.$this->purchaseNumber.' created.');

        $this->redirect(route('admin.purchases.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.purchases.create');
    }
}
