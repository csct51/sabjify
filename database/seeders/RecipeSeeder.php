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
                'steps' => [
                    'Wash and pat dry the lettuce, tomatoes, cucumber, capsicum and carrot.',
                    'Tear the lettuce into bite-sized pieces and add to a large bowl.',
                    'Slice the tomatoes, cucumber and capsicum; shred the carrot with a peeler.',
                    'Toss everything together, drizzle with olive oil and a pinch of salt.',
                    'Serve immediately for a crunchy, refreshing bowl.',
                ],
                'products' => ['lettuce-iceberg', 'tomato', 'cucumber', 'capsicum-mix', 'carrot'],
            ],
            [
                'title' => 'Green Detox Smoothie',
                'description' => 'Blend leafy greens, apple and mint into a cooling detox smoothie that refreshes and energises you first thing in the morning.',
                'image' => 'https://images.unsplash.com/photo-1553530666-ba11a7da3888?q=80&w=600&auto=format&fit=crop',
                'steps' => [
                    'Roughly chop the spinach, apple, cucumber and mint leaves.',
                    'Peel and grate a small piece of ginger.',
                    'Add everything to a blender with a cup of chilled water.',
                    'Blend until smooth and frothy.',
                    'Pour into a glass and enjoy fresh.',
                ],
                'products' => ['spinach-palak', 'fresh-apple', 'mint-leaves', 'cucumber', 'ginger'],
            ],
            [
                'title' => 'Fruit Energy Bowl',
                'description' => 'A naturally sweet bowl of bananas, apples, kiwi and berries — a perfect on-the-go breakfast loaded with antioxidants.',
                'image' => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?q=80&w=600&auto=format&fit=crop',
                'steps' => [
                    'Slice the banana, apple and kiwi into rounds.',
                    'Halve the blueberries and scoop the avocado into cubes.',
                    'Arrange the fruit in a bowl for a colourful layering.',
                    'Top with a drizzle of honey if you like it sweeter.',
                    'Dig in straight away while it is fresh.',
                ],
                'products' => ['banana-robusta', 'fresh-apple', 'kiwi', 'blueberry', 'avocado'],
            ],
            [
                'title' => 'Mediterranean Snack Platter',
                'description' => 'Sun-ripened tomatoes, avocado and capsicum tossed with fresh herbs for a light Mediterranean-style snack platter.',
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?q=80&w=600&auto=format&fit=crop',
                'steps' => [
                    'Dice the tomatoes and avocado into even cubes.',
                    'Slice the capsicum thinly and pick the coriander leaves.',
                    'Gently toss the vegetables with a squeeze of lemon.',
                    'Sprinkle fresh coriander on top.',
                    'Serve chilled as a light snack platter.',
                ],
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
                    'steps' => $recipe['steps'] ?? null,
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
