<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Category;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Purchase Report')]
class Purchases extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $supplier = '';

    public ?int $category = null;

    public string $search = '';

    public function mount(): void
    {
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSupplier(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function preset(string $range): void
    {
        $this->dateTo = now()->format('Y-m-d');

        $this->dateFrom = match ($range) {
            'today' => now()->format('Y-m-d'),
            'week' => now()->subDays(7)->format('Y-m-d'),
            default => now()->subDays(30)->format('Y-m-d'),
        };

        $this->resetPage();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Supplier>
     */
    #[Computed]
    public function suppliers(): Collection
    {
        return Supplier::orderBy('name')->get();
    }

    /**
     * Base query: purchase items in range.
     *
     * @return Builder<PurchaseItem>
     */
    private function scopedItems()
    {
        return PurchaseItem::query()
            ->with(['product.category', 'purchase'])
            ->whereHas('purchase', function ($query) {
                if ($this->dateFrom !== '') {
                    $query->whereDate('purchase_date', '>=', $this->dateFrom);
                }

                if ($this->dateTo !== '') {
                    $query->whereDate('purchase_date', '<=', $this->dateTo);
                }

                if ($this->supplier !== '') {
                    $query->where('supplier_name', $this->supplier);
                }
            })
            ->when($this->category, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $this->category)))
            ->when($this->search !== '', fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%')));
    }

    /**
     * @return array{spent: int, entries: int, products: int}
     */
    #[Computed]
    public function summary(): array
    {
        $spent = 0;
        $purchaseIds = [];
        $productIds = [];

        foreach ($this->scopedItems()->cursor() as $item) {
            $spent += (int) $item->line_total;
            $purchaseIds[$item->purchase_id] = true;
            $productIds[$item->product_id] = true;
        }

        return [
            'spent' => $spent,
            'entries' => count($purchaseIds),
            'products' => count($productIds),
        ];
    }

    /**
     * @return array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, spent: int}>
     */
    #[Computed]
    public function rows(): array
    {
        /** @var array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, spent: int}> $rows */
        $rows = [];

        foreach ($this->scopedItems()->cursor() as $item) {
            $key = (int) $item->product_id;

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name ?? '—',
                    'category' => $item->product?->category?->name ?? '—',
                    'unit' => (string) ($item->unit ?? '—'),
                    'qty' => 0.0,
                    'spent' => 0,
                ];
            }

            $rows[$key]['qty'] = round($rows[$key]['qty'] + (float) $item->qty, 3);
            $rows[$key]['spent'] += (int) $item->line_total;

            if ($rows[$key]['unit'] === '—' && $item->unit) {
                $rows[$key]['unit'] = $item->unit;
            }
        }

        usort($rows, fn ($a, $b) => $b['spent'] <=> $a['spent']);

        return array_values($rows);
    }

    public function render(): View
    {
        $rows = $this->rows();
        $perPage = 20;
        $total = count($rows);
        $page = max(1, min($this->getPage(), max(1, (int) ceil($total / $perPage))));

        $products = new LengthAwarePaginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page
        );

        return view('livewire.admin.reports.purchases', [
            'products' => $products,
        ]);
    }
}
