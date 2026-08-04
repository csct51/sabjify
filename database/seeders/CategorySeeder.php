<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Fruits', 'description' => 'Fresh seasonal fruits picked at their peak ripeness.', 'sort_order' => 1, 'image' => 'https://images.unsplash.com/photo-1630492729087-1a33255f5e64?q=80&w=600&auto=format&fit=crop'],
            ['name' => 'Vegetables', 'description' => 'Crisp farm-fresh vegetables delivered daily.', 'sort_order' => 2, 'image' => 'https://images.unsplash.com/photo-1589517576004-a198f11bc3a4?q=80&w=600&auto=format&fit=crop'],
            ['name' => 'Leafy Greens', 'description' => 'Nutritious greens full of vitamins and minerals.', 'sort_order' => 3, 'image' => 'https://images.unsplash.com/photo-1515356956468-873dd257f911?q=80&w=600&auto=format&fit=crop'],
            ['name' => 'Exotic Fruits', 'description' => 'Premium imported and exotic fruit varieties.', 'sort_order' => 4, 'image' => 'https://images.unsplash.com/photo-1571575173700-afb9492e6a50?q=80&w=600&auto=format&fit=crop'],
            ['name' => 'Herbs', 'description' => 'Aromatic fresh herbs to elevate your cooking.', 'sort_order' => 5, 'image' => 'https://images.unsplash.com/photo-1748792311906-9d0e8f88206c?q=80&w=600&auto=format&fit=crop'],
            ['name' => 'Root Vegetables', 'description' => 'Hearty root vegetables that keep the kitchen stocked.', 'sort_order' => 6, 'image' => 'https://images.unsplash.com/photo-1442897961655-905a8343c8eb?q=80&w=600&auto=format&fit=crop'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['slug' => Str::slug($category['name']), 'is_active' => true]
            );
        }
    }
}
