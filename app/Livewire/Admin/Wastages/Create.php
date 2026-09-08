<?php

namespace App\Livewire\Admin\Wastages;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Wastage;
use App\Models\WastageItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Create Wastage')]
class Create extends Component
{
    /** @var array<int, array{product_id: ?int, unit: string, qty: string}> */
    public array $rows = [];

    public string $reason = 'Expired';

    public string $customReason = '';

    public string $wastageDate = '';

    public string $remark = '';

    public string $wastageNumber = '';

    public ?int $formProductId = null;

    public string $formQty = '';

    public string $formError = '';

    public function mount(): void
    {
        $this->wastageNumber = $this->generateWastageNumber();
        $this->wastageDate = now()->format('Y-m-d');
    }

    private function generateWastageNumber(): string
    {
        $prefix = 'WST-';
        $max = Wastage::query()
            ->where('wastage_number', 'like', $prefix.'%')
            ->pluck('wastage_number')
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

    public function addProduct(): void
    {
        $this->formError = '';

        try {
            $this->validate([
                'formProductId' => ['required', 'integer', 'exists:products,id'],
            ], [
                'formProductId.required' => 'Please select a product.',
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

        // Check stock
        $baseQty = Unit::toBaseQty($unit, (float) $this->formQty);

        if ($product && (float) $product->current_stock < $baseQty - 1e-9) {
            $this->addError('formQty', 'Insufficient stock. Only '.$product->current_stock.' left.');
            $this->formError = 'Insufficient stock for '.$product->name;

            return;
        }

        $this->rows[] = [
            'product_id' => $this->formProductId,
            'unit' => $unit,
            'qty' => $this->formQty,
        ];

        $this->formProductId = null;
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
            'rows.*.qty' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'wastageDate' => ['required', 'date'],
            'reason' => ['required', 'string', 'in:Expired,Damaged,Spoiled,Other'],
            'customReason' => ['required_if:reason,Other', 'nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($this->rows as $row) {
            $qtyFloat = (float) $row['qty'];

            if (Unit::integerOnlyFor($row['unit'] ?? '') && floor($qtyFloat) != $qtyFloat) {
                throw ValidationException::withMessages(['rows' => 'Qty for piece must be a whole number.']);
            }
        }

        $effectiveReason = $this->reason === 'Other' ? $this->customReason : $this->reason;

        DB::transaction(function () use ($effectiveReason) {
            $totalQty = round(collect($this->rows)->sum(fn ($r) => (float) $r['qty']), 3);
            $wastageNumber = $this->generateWastageNumber();

            // Check all rows have sufficient stock before decrementing
            foreach ($this->rows as $row) {
                $product = Product::whereKey($row['product_id'])->lockForUpdate()->first();
                $baseQty = Unit::toBaseQty($row['unit'], (float) $row['qty']);

                if ($product && (float) $product->current_stock < $baseQty - 1e-9) {
                    throw ValidationException::withMessages(['rows' => 'Insufficient stock for '.$product->name.'. Only '.$product->current_stock.' left.']);
                }
            }

            $wastage = Wastage::create([
                'wastage_number' => $wastageNumber,
                'wastage_date' => $this->wastageDate,
                'reason' => $effectiveReason,
                'remark' => $this->remark ?: null,
                'total_qty' => $totalQty,
                'created_by' => auth('admin')->id(),
            ]);

            foreach ($this->rows as $row) {
                $product = Product::find($row['product_id']);
                $qtyFloat = (float) $row['qty'];
                $baseQty = Unit::toBaseQty($row['unit'], $qtyFloat);

                WastageItem::create([
                    'wastage_id' => $wastage->id,
                    'product_id' => $row['product_id'],
                    'unit' => $row['unit'],
                    'qty' => $qtyFloat,
                    'base_qty' => $baseQty,
                ]);

                $product?->decrement('current_stock', $baseQty);
            }
        });

        session()->flash('success', 'Wastage '.$this->wastageNumber.' recorded.');

        $this->redirect(route('admin.wastages.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.wastages.create');
    }
}
