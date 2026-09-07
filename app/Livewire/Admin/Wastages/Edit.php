<?php

namespace App\Livewire\Admin\Wastages;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Wastage;
use App\Models\WastageItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Edit Wastage')]
class Edit extends Component
{
    public $wastage = null;

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

    public function mount($wastage = null): void
    {
        if ($wastage !== null && ! $wastage instanceof Wastage) {
            $wastage = Wastage::with('items')->find($wastage);
        }

        $this->wastage = $wastage;

        if ($wastage instanceof Wastage) {
            $this->wastageNumber = $wastage->wastage_number ?? 'WST-001';
            $this->wastageDate = $wastage->wastage_date ? $wastage->wastage_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->reason = in_array($wastage->reason, ['Expired', 'Damaged', 'Spoiled'], true) ? $wastage->reason : 'Other';
            $this->customReason = $wastage->reason && ! in_array($wastage->reason, ['Expired', 'Damaged', 'Spoiled'], true) ? $wastage->reason : '';
            $this->remark = $wastage->remark ?? '';
            $wastage->loadMissing('items');
            $this->rows = $wastage->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'unit' => $item->unit ?? 'kg',
                'qty' => (string) ($item->qty ?? ''),
            ])->toArray();
        } else {
            $this->wastageNumber = 'WST-001';
            $this->wastageDate = now()->format('Y-m-d');
        }

        if (empty($this->rows)) {
            $this->rows[] = ['product_id' => null, 'unit' => 'kg', 'qty' => ''];
        }
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();
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
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function delete(): void
    {
        if (! $this->wastage) {
            return;
        }

        DB::transaction(function () {
            $wastage = $this->wastage;
            $wastage->loadMissing('items');
            foreach ($wastage->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $baseQty = Unit::storedBaseQty($oldItem->unit ?? '', (float) $oldItem->qty, (float) $oldItem->base_qty);
                $product?->increment('current_stock', $baseQty);
            }
            $wastage->delete();
        });

        session()->flash('success', 'Wastage deleted.');

        $this->redirect(route('admin.wastages.index'), navigate: true);
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
            $wastage = $this->wastage;
            $totalQty = round(collect($this->rows)->sum(fn ($r) => (float) $r['qty']), 3);

            // Old base per product (plausibility-guarded).
            $wastage->loadMissing('items');
            $oldBase = [];

            foreach ($wastage->items as $oldItem) {
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

            // Check net-new wastage availability before moving anything.
            foreach (array_unique([...array_keys($oldBase), ...array_keys($newBase)]) as $productId) {
                $diff = round(($newBase[$productId] ?? 0) - ($oldBase[$productId] ?? 0), 3);

                if ($diff > 0) {
                    $product = Product::whereKey($productId)->lockForUpdate()->first();

                    if ($product && (float) $product->current_stock < $diff - 1e-9) {
                        throw ValidationException::withMessages(['rows' => 'Insufficient stock for '.$product->name.'. Only '.$product->current_stock.' left.']);
                    }
                }
            }

            // Net movement only: untouched products (and header-only edits)
            // move no stock at all.
            foreach (array_unique([...array_keys($oldBase), ...array_keys($newBase)]) as $productId) {
                $diff = round(($newBase[$productId] ?? 0) - ($oldBase[$productId] ?? 0), 3);

                if ($diff > 0) {
                    Product::find($productId)?->decrement('current_stock', $diff);
                } elseif ($diff < 0) {
                    Product::find($productId)?->increment('current_stock', -$diff);
                }
            }

            $wastage->items()->delete();

            $wastage->update([
                'wastage_date' => $this->wastageDate,
                'reason' => $effectiveReason,
                'remark' => $this->remark ?: null,
                'total_qty' => $totalQty,
            ]);

            foreach ($this->rows as $row) {
                $qtyFloat = (float) $row['qty'];
                $baseQty = Unit::toBaseQty($row['unit'], $qtyFloat);

                WastageItem::create([
                    'wastage_id' => $wastage->id,
                    'product_id' => $row['product_id'],
                    'unit' => $row['unit'],
                    'qty' => $qtyFloat,
                    'base_qty' => $baseQty,
                ]);
            }
        });

        session()->flash('success', 'Wastage '.$this->wastageNumber.' updated.');

        $this->redirect(route('admin.wastages.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.wastages.edit');
    }
}
