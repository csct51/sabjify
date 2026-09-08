<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BasketSeeder extends Seeder
{
    /**
     * Basket shells (names, copy, fixed sabjify prices) with ZERO product
     * links — the sample-only slugs these used to attach no longer exist
     * after the real catalog import. Admin fills products in the UI.
     *
     * firstOrCreate: re-runs never clobber admin edits or detach links.
     */
    private const DUMMY_PRODUCTS = [
        'heart-care-basket' => ['apple', 'banana', 'carrot', 'beetroot'],
        'bp-friendly-basket' => ['banana', 'beetroot', 'cucumber', 'carrot'],
        'diabetes-friendly-basket' => ['apple', 'cucumber', 'carrot', 'tomato'],
        'weight-management-basket' => ['cucumber', 'carrot', 'tomato', 'cabbage'],
        'liver-friendly-basket' => ['beetroot', 'carrot', 'cucumber', 'lemon'],
        'family-wellness-basket' => ['apple', 'banana', 'tomato', 'onion', 'carrot', 'mango'],
        'essential-basket' => ['tomato', 'onion', 'carrot', 'cucumber'],
        'smart-basket' => ['tomato', 'onion', 'carrot', 'cucumber', 'apple', 'banana'],
        'premium-basket' => ['apple', 'banana', 'mango', 'papaya'],
        'complete-basket' => ['tomato', 'onion', 'carrot', 'cucumber', 'apple', 'banana', 'mango', 'lemon'],
    ];

    public function run(): void
    {
        $baskets = [
            [
                'name' => 'Heart Care Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'A heart-friendly mix of fresh fruits, leafy greens and herbs to support a healthy heart.',
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'BP-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Potassium-rich produce and leafy greens to help manage blood pressure naturally.',
                'image' => 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'Diabetes-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Low-GI fruits, fibre-rich greens and herbs to keep blood sugar steady.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'Weight Management Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'High-fibre, low-calorie picks that keep you full longer while you reach your goals.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'Liver-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Cruciferous greens, beetroot and citrus to aid detoxification and liver health.',
                'image' => 'https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'Family Wellness Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'A wholesome mix of everyday fruits and vegetables the whole family can enjoy.',
                'image' => 'https://images.unsplash.com/photo-1543353071-873f17a7a088?q=80&w=600&auto=format&fit=crop',
            ],
            [
                'name' => 'Essential Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'The everyday essentials — fresh daily staples to keep your kitchen stocked.',
                'price' => 299,
                'mrp' => 359,
            ],
            [
                'name' => 'Smart Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'A smart mix of kitchen staples plus seasonal fruits and greens for balanced weekly shopping.',
                'image' => 'https://images.unsplash.com/photo-1543168256-418811576931?q=80&w=600&auto=format&fit=crop',
                'price' => 499,
                'mrp' => 599,
            ],
            [
                'name' => 'Premium Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'Premium fruits and premium produce — exotic picks and top-grade vegetables.',
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=600&auto=format&fit=crop',
                'price' => 799,
                'mrp' => 959,
            ],
            [
                'name' => 'Complete Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'The complete sabjify experience — every fruit and vegetable you love, in one basket.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=600&auto=format&fit=crop',
                'price' => 999,
                'mrp' => 1199,
            ],
        ];

        foreach ($baskets as $index => $basket) {
            $attributes = [
                'name' => $basket['name'],
                'type' => $basket['type'],
                'description' => $basket['description'],
                'price' => $basket['price'] ?? 0,
                'mrp' => $basket['mrp'] ?? null,
                'is_active' => true,
                'sort_order' => $index,
            ];

            if (isset($basket['image'])) {
                $attributes['image'] = $basket['image'];
            }

            $model = Basket::firstOrCreate(
                ['slug' => Str::slug($basket['name'])],
                $attributes
            );

            $this->attachDummies($model);
        }
    }

    /**
     * Attach placeholder products only to baskets that have none — admin
     * filled baskets (and re-runs) are never touched.
     */
    private function attachDummies(Basket $model): void
    {
        if ($model->products()->exists()) {
            return;
        }

        $ids = Product::whereIn('slug', self::DUMMY_PRODUCTS[$model->slug] ?? [])->pluck('id');

        if ($ids->isNotEmpty()) {
            $model->products()->sync($ids);
        }
    }
}
