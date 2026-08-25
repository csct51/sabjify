<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class ProductSearch
{
    /**
     * @return array{items: Collection<int, Product>, total: int, hasMore: bool}
     */
    public static function search(string $term, ?string $categorySlug = null, string $sort = 'latest', int $page = 1, int $perPage = 12): array
    {
        $term = trim($term);

        if ($term === '') {
            return self::browse($categorySlug, $sort, $page, $perPage);
        }

        $needle = Str::ascii(mb_strtolower($term));
        $threshold = max(2, intdiv(mb_strlen($term), 4));

        $candidates = Product::active()
            ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $categorySlug)))
            ->select(['id', 'name', 'category_id', 'alternate_names', 'created_at', 'price', 'sort_order', 'slug'])
            ->limit(2000)
            ->get();

        $matched = $candidates
            ->map(fn (Product $product) => ['product' => $product, 'score' => self::score($product, $needle, $threshold)])
            ->reject(fn (array $row) => $row['score'] === null)
            ->sortBy(fn (array $row) => $row['score'])
            ->values();

        $matched = $matched->sort(function (array $a, array $b) use ($sort) {
            if ($a['score'] !== $b['score']) {
                return $a['score'] <=> $b['score'];
            }

            return self::secondarySort($a['product'], $b['product'], $sort);
        })->values();

        $total = $matched->count();
        $pageItems = $matched->forPage($page, $perPage);
        $ids = $pageItems->pluck('product.id')->all();

        $items = $ids !== []
            ? Product::active()
                ->with(['category', 'units'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn (Product $product) => array_search($product->id, $ids))
                ->values()
            : new Collection;

        return [
            'items' => $items,
            'total' => $total,
            'hasMore' => $total > ($page - 1) * $perPage + $items->count(),
        ];
    }

    private static function score(Product $product, string $needle, int $threshold): ?int
    {
        $name = Str::ascii(mb_strtolower($product->name));

        if (str_contains($name, $needle)) {
            return 0;
        }

        foreach ((array) $product->alternate_names as $alternate) {
            if (str_contains(Str::ascii(mb_strtolower($alternate)), $needle)) {
                return 1;
            }
        }

        $best = levenshtein($name, $needle);

        foreach ((array) $product->alternate_names as $alternate) {
            $best = min($best, levenshtein(Str::ascii(mb_strtolower($alternate)), $needle));
        }

        return $best <= $threshold ? (2 + $best) : null;
    }

    private static function secondarySort(Product $a, Product $b, string $sort): int
    {
        return match ($sort) {
            'price_low' => $a->price <=> $b->price,
            'price_high' => $b->price <=> $a->price,
            'popular' => $b->sort_order <=> $a->sort_order,
            'name' => strcmp($a->name, $b->name),
            default => $b->created_at <=> $a->created_at,
        };
    }

    /**
     * @return array{items: Collection<int, Product>, total: int, hasMore: bool}
     */
    private static function browse(?string $categorySlug, string $sort, int $page, int $perPage): array
    {
        $query = Product::active()
            ->with(['category', 'units'])
            ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($query) => $query->where('slug', $categorySlug)))
            ->when($sort, function ($query) use ($sort) {
                match ($sort) {
                    'price_low' => $query->orderBy('price'),
                    'price_high' => $query->orderByDesc('price'),
                    'popular' => $query->orderByDesc('sort_order'),
                    'name' => $query->orderBy('name'),
                    default => $query->latest(),
                };
            });

        $total = $query->count();
        $items = $query->forPage($page, $perPage)->get();

        return [
            'items' => $items,
            'total' => $total,
            'hasMore' => $total > ($page - 1) * $perPage + $items->count(),
        ];
    }
}
