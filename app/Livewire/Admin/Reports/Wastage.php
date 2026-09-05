<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Category;
use App\Models\WastageItem;
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
#[Title('Wastage Report')]
class Wastage extends Component
{
    use WithPagination;

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $reason = '';

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

    public function updatingReason(): void
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
     * @return array<int, string>
     */
    #[Computed]
    public function reasons(): array
    {
        return \App\Models\Wastage::query()
            ->distinct()
            ->orderBy('reason')
            ->pluck('reason')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Base query: wastage items in range.
     *
     * @return Builder<WastageItem>
     */
    private function scopedItems()
    {
        return WastageItem::query()
            ->with(['product.category', 'wastage'])
            ->whereHas('wastage', function ($query) {
                if ($this->dateFrom !== '') {
                    $query->whereDate('wastage_date', '>=', $this->dateFrom);
                }

                if ($this->dateTo !== '') {
                    $query->whereDate('wastage_date', '<=', $this->dateTo);
                }

                if ($this->reason !== '') {
                    $query->where('reason', $this->reason);
                }
            })
            ->when($this->category, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $this->category)))
            ->when($this->search !== '', fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%')));
    }

    /**
     * @return array{entries: int, products: int}
     */
    #[Computed]
    public function summary(): array
    {
        $wastageIds = [];
        $productIds = [];

        foreach ($this->scopedItems()->cursor() as $item) {
            $wastageIds[$item->wastage_id] = true;
            $productIds[$item->product_id] = true;
        }

        return [
            'entries' => count($wastageIds),
            'products' => count($productIds),
        ];
    }

    /**
     * @return array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, entries: int}>
     */
    #[Computed]
    public function rows(): array
    {
        /** @var array<int, array{product_id: int|null, name: string, category: string, unit: string, qty: float, entries: int}> $rows */
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
                    'entries' => 0,
                ];
            }

            $rows[$key]['qty'] = round($rows[$key]['qty'] + (float) $item->qty, 3);
            $rows[$key]['entries']++;

            if ($rows[$key]['unit'] === '—' && $item->unit) {
                $rows[$key]['unit'] = $item->unit;
            }
        }

        usort($rows, fn ($a, $b) => $b['base_qty'] <=> $a['base_qty']);

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

        return view('livewire.admin.reports.wastage', [
            'products' => $products,
        ]);
    }
}
