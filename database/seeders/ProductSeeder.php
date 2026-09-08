<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Real catalog merged from the owner's mandi sheets (docs/Book*.csv →
     * database/seeders/data/products.csv).
     *
     * Products only: uniform gram base, a single 1 kg unit at placeholder
     * price 100, zero stock. Admin enters real prices/units in the product
     * form and stocks via purchases.
     *
     * firstOrCreate + fill-if-empty backfill: re-runs never clobber admin
     * edits, but still fill alternate names on rows seeded before the
     * names existed (no fresh needed).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/products.csv');

        if (! is_file($path)) {
            throw new \RuntimeException('Product seed CSV missing: '.$path);
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Product seed CSV unreadable: '.$path);
        }

        $header = fgetcsv($handle);

        if ($header !== ['name', 'category', 'alternate_names']) {
            fclose($handle);

            throw new \RuntimeException('Product seed CSV header must be exactly: name,category,alternate_names');
        }

        $sort = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim((string) ($row[0] ?? ''));
            $categoryName = trim((string) ($row[1] ?? ''));
            $alternateNames = trim((string) ($row[2] ?? ''));

            if ($name === '' || $categoryName === '') {
                continue;
            }

            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                fclose($handle);

                throw new \RuntimeException("Unknown category '{$categoryName}' for product '{$name}' — seed categories first.");
            }

            $model = Product::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => "Fresh {$name} sourced from trusted local growers.",
                    'unit' => '1 kg',
                    'base_unit' => 'g',
                    'price' => 100,
                    'mrp' => null,
                    'current_stock' => 0,
                    'low_stock' => 1000,
                    'image' => null,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => $sort++,
                ]
            );

            $model->units()->firstOrCreate(
                ['unit' => '1 kg'],
                [
                    'price' => 100,
                    'mrp' => null,
                    'in_stock' => true,
                    'sort_order' => 0,
                ]
            );

            if ($alternateNames !== '' && empty($model->alternate_names)) {
                $model->update(['alternate_names' => $alternateNames]);
            }
        }

        fclose($handle);
    }
}
