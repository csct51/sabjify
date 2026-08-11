<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            [
                'title' => 'Rainbow Garden Salad',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=600&auto=format&fit=crop',
                'products' => ['lettuce-iceberg', 'tomato', 'cucumber', 'capsicum-mix', 'carrot'],
            ],
            [
                'title' => 'Green Detox Smoothie',
                'image' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?q=80&w=600&auto=format&fit=crop',
                'products' => ['spinach-palak', 'fresh-apple', 'mint-leaves', 'cucumber', 'ginger'],
            ],
            [
                'title' => 'Fruit Energy Bowl',
                'image' => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?q=80&w=600&auto=format&fit=crop',
                'products' => ['banana-robusta', 'fresh-apple', 'kiwi', 'blueberry', 'avocado'],
            ],
            [
                'title' => 'Mediterranean Snack Platter',
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?q=80&w=600&auto=format&fit=crop',
                'products' => ['avocado', 'tomato', 'capsicum-mix', 'lettuce-iceberg', 'coriander'],
            ],
        ];

        foreach ($recipes as $index => $recipe) {
            $products = Product::whereIn('slug', $recipe['products'])->get();

            $model = Recipe::updateOrCreate(
                ['slug' => Str::slug($recipe['title'])],
                [
                    'title' => $recipe['title'],
                    'image' => $recipe['image'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            $model->products()->sync($products->pluck('id'));
        }
    }
}
