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
                'description' => 'A crisp, colourful salad packed with fresh farm vegetables, a drizzle away from your daily dose of vitamins.',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=600&auto=format&fit=crop',
                'products' => ['lettuce-iceberg', 'tomato', 'cucumber', 'capsicum-mix', 'carrot'],
            ],
            [
                'title' => 'Green Detox Smoothie',
                'description' => 'Blend leafy greens, apple and mint into a cooling detox smoothie that refreshes and energises you first thing in the morning.',
                'image' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?q=80&w=600&auto=format&fit=crop',
                'products' => ['spinach-palak', 'fresh-apple', 'mint-leaves', 'cucumber', 'ginger'],
            ],
            [
                'title' => 'Fruit Energy Bowl',
                'description' => 'A naturally sweet bowl of bananas, apples, kiwi and berries — a perfect on-the-go breakfast loaded with antioxidants.',
                'image' => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?q=80&w=600&auto=format&fit=crop',
                'products' => ['banana-robusta', 'fresh-apple', 'kiwi', 'blueberry', 'avocado'],
            ],
            [
                'title' => 'Mediterranean Snack Platter',
                'description' => 'Sun-ripened tomatoes, avocado and capsicum tossed with fresh herbs for a light Mediterranean-style snack platter.',
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
                    'description' => $recipe['description'] ?? null,
                    'image' => $recipe['image'],
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            $sync = [];

            foreach ($products as $product) {
                $sync[$product->id] = [
                    'product_unit_id' => $product->defaultUnit()?->id,
                ];
            }

            $model->products()->sync($sync);
        }
    }
}
