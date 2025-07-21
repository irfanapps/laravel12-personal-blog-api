<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            ['name' => 'Technology', 'description' => 'Posts about technology and programming'],
            ['name' => 'Travel', 'description' => 'Travel experiences and guides'],
            ['name' => 'Food', 'description' => 'Recipes and restaurant reviews'],
            ['name' => 'Lifestyle', 'description' => 'Daily life and personal experiences'],
            ['name' => 'Health', 'description' => 'Health tips and fitness advice'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_visible' => true
            ]);
        }
    }
}
