<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Unit;
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
#[Title('Selling Report')]
class Selling extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

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
     * Base query: product rows of non-cancelled orders in range.
     *
     * @return Builder<OrderItem>
     */
    private function scopedItems()
    {
        return OrderItem::query()
            ->with(['product.category'])
            ->whereNotNull('product_id')
            ->whereHas('order', function ($query) {
                $query->where('status', '!=', Order::STATUS_CANCELLED);

                if ($this->dateFrom !== '') {
                    $query->whereDate('created_at', '>=', $this->dateFrom);
                }

                if ($this->dateTo !== '') {
                    $query->whereDate('created_at', '<=', $this->dateTo);
                }
            })
            ->when($this->category, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $this->category)))
            ->when($this->search !== '', fn ($q) => $q->where('product_name', 'like', '%'.$this->search.'%')
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%')));
    }

    /**
     * @return array{revenue: int, orders: int, qty: float}
     */
    #[Computed]
    public function summary(): array
    {
        $revenue = 0;
        $qty = 0.0;
        $orderIds = [];

        foreach ($this->scopedItems()->cursor() as $item) {
            $revenue += (int) $item->total;
            $qty = round($qty + (float) $item->quantity, 3);
            $orderIds[$item->order_id] = true;
        }

        return [
            'revenue' => $revenue,
            'orders' => count($orderIds),
            'qty' => $qty,
        ];
    }

    /**
     * @return array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, revenue: int}>
     */
    #[Computed]
    public function rows(): array
    {
        /** @var array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, revenue: int}> $rows */
        $rows = [];

        foreach ($this->scopedItems()->with('product')->cursor() as $item) {
            $key = (int) $item->product_id;
            // Normalize mixed selling denominations (500 g, 1 kg) into the
            // product's purchase unit (kg, piece) for display.
            $purchaseUnit = $item->product?->purchaseUnit() ?? (string) ($item->unit ?? '—');
            $purchaseFactor = Unit::factorFor($purchaseUnit) ?? 1.0;
            $itemFactor = $item->unit ? (Unit::factorFor($item->unit) ?? 1.0) : 1.0;

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'category' => $item->product?->category?->name ?? '—',
                    'unit' => $purchaseUnit,
                    'qty' => 0.0,
                    'revenue' => 0,
                ];
            }

            $rows[$key]['unit'] = $purchaseUnit;
            $rows[$key]['qty'] = round($rows[$key]['qty'] + (float) $item->quantity * $itemFactor / ($purchaseFactor > 0 ? $purchaseFactor : 1.0), 3);
            $rows[$key]['revenue'] += (int) $item->total;
        }

        usort($rows, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

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

        return view('livewire.admin.reports.selling', [
            'products' => $products,
        ]);
    }
}
