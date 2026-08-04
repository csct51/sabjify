<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['category' => 'Fruits', 'name' => 'Fresh Apple', 'unit' => '1 kg', 'price' => 180, 'mrp' => 220, 'stock' => 80, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1630492729087-1a33255f5e64?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Fruits', 'name' => 'Banana (Robusta)', 'unit' => '1 dozen', 'price' => 60, 'mrp' => 75, 'stock' => 120, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1668762924684-a9753a0a887c?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Fruits', 'name' => 'Sweet Orange', 'unit' => '1 kg', 'price' => 90, 'mrp' => 110, 'stock' => 70, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1579007002510-6121ca6dbbc7?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Fruits', 'name' => 'Seedless Grapes', 'unit' => '500 g', 'price' => 95, 'mrp' => 120, 'stock' => 45, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1698703428304-5ea0e245e266?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Fruits', 'name' => 'Watermelon', 'unit' => '1 pc', 'price' => 65, 'mrp' => 80, 'stock' => 30, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Vegetables', 'name' => 'Tomato', 'unit' => '1 kg', 'price' => 40, 'mrp' => 55, 'stock' => 150, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1589517576004-a198f11bc3a4?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Vegetables', 'name' => 'Onion', 'unit' => '1 kg', 'price' => 45, 'mrp' => 55, 'stock' => 200, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1678568800071-afd8c6ec20a7?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Vegetables', 'name' => 'Potato', 'unit' => '1 kg', 'price' => 30, 'mrp' => 38, 'stock' => 250, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1688043046229-fe8bc88f8b99?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Vegetables', 'name' => 'Capsicum (Mix)', 'unit' => '500 g', 'price' => 55, 'mrp' => 70, 'stock' => 60, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1741518165791-df31747b8d8b?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Vegetables', 'name' => 'Cucumber', 'unit' => '1 kg', 'price' => 35, 'mrp' => 45, 'stock' => 90, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1599448191905-7bedab8ced59?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Leafy Greens', 'name' => 'Spinach (Palak)', 'unit' => '1 bunch', 'price' => 25, 'mrp' => 30, 'stock' => 40, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1519995672084-d21490e86ba6?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Leafy Greens', 'name' => 'Coriander', 'unit' => '1 bunch', 'price' => 15, 'mrp' => 20, 'stock' => 55, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1776089770931-e422e57f760c?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Leafy Greens', 'name' => 'Lettuce (Iceberg)', 'unit' => '1 pc', 'price' => 45, 'mrp' => 60, 'stock' => 25, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1515356956468-873dd257f911?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Exotic Fruits', 'name' => 'Avocado', 'unit' => '2 pcs', 'price' => 120, 'mrp' => 150, 'stock' => 20, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1560155016-bd4879ae8f21?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Exotic Fruits', 'name' => 'Dragon Fruit', 'unit' => '1 pc', 'price' => 90, 'mrp' => 110, 'stock' => 18, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1623030235422-07f96401f5ea?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Exotic Fruits', 'name' => 'Kiwi', 'unit' => '4 pcs', 'price' => 140, 'mrp' => 170, 'stock' => 22, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1571575173700-afb9492e6a50?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Exotic Fruits', 'name' => 'Blueberry', 'unit' => '125 g', 'price' => 250, 'mrp' => 300, 'stock' => 12, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1657999846716-9ce28c88132e?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Herbs', 'name' => 'Mint Leaves', 'unit' => '1 bunch', 'price' => 20, 'mrp' => 25, 'stock' => 35, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1748792311906-9d0e8f88206c?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Herbs', 'name' => 'Basil (Tulsi)', 'unit' => '1 bunch', 'price' => 30, 'mrp' => 38, 'stock' => 28, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1776257217010-1c2e92207f2a?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Herbs', 'name' => 'Curry Leaves', 'unit' => '1 bunch', 'price' => 10, 'mrp' => 15, 'stock' => 40, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1535189487909-a262ad10c165?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Root Vegetables', 'name' => 'Carrot', 'unit' => '500 g', 'price' => 40, 'mrp' => 50, 'stock' => 65, 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1442897961655-905a8343c8eb?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Root Vegetables', 'name' => 'Beetroot', 'unit' => '500 g', 'price' => 35, 'mrp' => 45, 'stock' => 50, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1757513671720-e95bb606ac82?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Root Vegetables', 'name' => 'Radish (White)', 'unit' => '500 g', 'price' => 25, 'mrp' => 32, 'stock' => 42, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1561623801-04403b1575b4?q=80&w=600&auto=format&fit=crop'],
            ['category' => 'Root Vegetables', 'name' => 'Ginger', 'unit' => '250 g', 'price' => 45, 'mrp' => 55, 'stock' => 48, 'featured' => false, 'image' => 'https://images.unsplash.com/photo-1758055660285-d32b7b3b1e77?q=80&w=600&auto=format&fit=crop'],
        ];

        foreach ($products as $product) {
            $category = Category::where('name', $product['category'])->first();

            Product::updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'slug' => Str::slug($product['name']),
                    'unit' => $product['unit'],
                    'price' => $product['price'],
                    'mrp' => $product['mrp'],
                    'stock' => $product['stock'],
                    'is_active' => true,
                    'is_featured' => $product['featured'],
                    'image' => $product['image'],
                    'description' => "Farm-fresh {$product['name']} sourced directly from trusted local growers. Handpicked daily and delivered to your doorstep.",
                ]
            );
        }
    }
}
