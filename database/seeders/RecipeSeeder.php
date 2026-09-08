<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecipeSeeder extends Seeder
{
    /**
     * Recipe shells (titles, copy, steps) with ZERO product links — the
     * sample-only slugs these used to attach no longer exist after the
     * real catalog import. Admin fills ingredients in the UI.
     *
     * firstOrCreate: re-runs never clobber admin edits or detach links.
     */
    private const DUMMY_PRODUCTS = [
        'rainbow-garden-salad' => ['tomato', 'cucumber', 'carrot', 'cabbage'],
        'green-detox-smoothie' => ['apple', 'banana', 'ginger', 'lemon'],
        'fruit-energy-bowl' => ['banana', 'apple', 'mango', 'papaya'],
        'mediterranean-snack-platter' => ['tomato', 'cucumber', 'carrot', 'lemon', 'coriander-leaves'],
    ];

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
            ],
        ];

        foreach ($recipes as $index => $recipe) {
            $model = Recipe::firstOrCreate(
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

            $this->attachDummies($model);
        }
    }

    /**
     * Attach placeholder ingredients only to recipes that have none — admin
     * filled recipes (and re-runs) are never touched.
     */
    private function attachDummies(Recipe $model): void
    {
        if ($model->products()->exists()) {
            return;
        }

        $products = Product::whereIn('slug', self::DUMMY_PRODUCTS[$model->slug] ?? [])->get();

        if ($products->isEmpty()) {
            return;
        }

        $sync = [];

        foreach ($products as $product) {
            $sync[$product->id] = [
                'product_unit_id' => $product->defaultUnit()?->id,
            ];
        }

        $model->products()->sync($sync);
    }
}
