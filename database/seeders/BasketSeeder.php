<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BasketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baskets = [
            [
                'name' => 'Heart Care Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'A heart-friendly mix of fresh fruits, leafy greens and herbs to support a healthy heart.',
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=600&auto=format&fit=crop',
                'products' => ['fresh-apple', 'spinach-palak', 'avocado', 'tomato', 'banana-robusta', 'carrot', 'mint-leaves', 'ginger'],
            ],
            [
                'name' => 'BP-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Potassium-rich produce and leafy greens to help manage blood pressure naturally.',
                'image' => 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?q=80&w=600&auto=format&fit=crop',
                'products' => ['banana-robusta', 'spinach-palak', 'beetroot', 'sweet-orange', 'kiwi', 'curry-leaves', 'cucumber', 'lettuce-iceberg'],
            ],
            [
                'name' => 'Diabetes-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Low-GI fruits, fibre-rich greens and herbs to keep blood sugar steady.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=600&auto=format&fit=crop',
                'products' => ['spinach-palak', 'fresh-apple', 'kiwi', 'blueberry', 'avocado', 'cucumber', 'tomato', 'ginger'],
            ],
            [
                'name' => 'Weight Management Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'High-fibre, low-calorie picks that keep you full longer while you reach your goals.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=600&auto=format&fit=crop',
                'products' => ['cucumber', 'lettuce-iceberg', 'tomato', 'carrot', 'capsicum-mix', 'spinach-palak', 'coriander', 'radish-white'],
            ],
            [
                'name' => 'Liver-Friendly Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'Cruciferous greens, beetroot and citrus to aid detoxification and liver health.',
                'image' => 'https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=600&auto=format&fit=crop',
                'products' => ['beetroot', 'sweet-orange', 'ginger', 'mint-leaves', 'coriander', 'radish-white', 'curry-leaves', 'carrot'],
            ],
            [
                'name' => 'Family Wellness Basket',
                'type' => Basket::TYPE_WELLNESS,
                'description' => 'A wholesome mix of everyday fruits and vegetables the whole family can enjoy.',
                'image' => 'https://images.unsplash.com/photo-1543353071-873f17a7a088?q=80&w=600&auto=format&fit=crop',
                'products' => ['fresh-apple', 'banana-robusta', 'tomato', 'onion', 'potato', 'spinach-palak', 'carrot', 'cucumber', 'sweet-orange', 'capsicum-mix'],
            ],
            [
                'name' => 'Essential Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'The everyday essentials — fresh daily staples to keep your kitchen stocked.',
                'products' => ['tomato', 'onion', 'potato', 'carrot', 'spinach-palak', 'cucumber', 'coriander', 'curry-leaves'],
                'price' => 299,
            ],
            [
                'name' => 'Smart Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'A smart mix of kitchen staples plus seasonal fruits and greens for balanced weekly shopping.',
                'image' => 'https://images.unsplash.com/photo-1543168256-418811576931?q=80&w=600&auto=format&fit=crop',
                'products' => ['tomato', 'onion', 'potato', 'carrot', 'cucumber', 'capsicum-mix', 'banana-robusta', 'sweet-orange', 'ginger', 'mint-leaves'],
                'price' => 499,
            ],
            [
                'name' => 'Premium Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'Premium fruits and premium produce — exotic picks and top-grade vegetables.',
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?q=80&w=600&auto=format&fit=crop',
                'products' => ['fresh-apple', 'avocado', 'kiwi', 'blueberry', 'seedless-grapes', 'sweet-orange', 'watermelon', 'lettuce-iceberg', 'capsicum-mix', 'tomato'],
                'price' => 799,
            ],
            [
                'name' => 'Complete Basket',
                'type' => Basket::TYPE_SABJIFY,
                'description' => 'The complete sabjify experience — every fruit and vegetable you love, in one basket.',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=600&auto=format&fit=crop',
                'products' => ['fresh-apple', 'banana-robusta', 'avocado', 'kiwi', 'blueberry', 'seedless-grapes', 'sweet-orange', 'watermelon', 'tomato', 'onion', 'potato', 'carrot', 'spinach-palak', 'cucumber'],
                'price' => 999,
            ],
        ];

        foreach ($baskets as $index => $basket) {
            $products = Product::whereIn('slug', $basket['products'])->get();

            if ($products->isEmpty()) {
                continue;
            }

            $basketPrice = $basket['price'] ?? (int) round($products->sum('price') * 0.9);

            $attributes = [
                'name' => $basket['name'],
                'type' => $basket['type'],
                'description' => $basket['description'],
                'price' => $basketPrice,
                'is_active' => true,
                'sort_order' => $index,
            ];

            if (isset($basket['image'])) {
                $attributes['image'] = $basket['image'];
            }

            $model = Basket::updateOrCreate(
                ['slug' => Str::slug($basket['name'])],
                $attributes
            );

            $model->products()->sync($products->pluck('id'));
        }
    }
}
