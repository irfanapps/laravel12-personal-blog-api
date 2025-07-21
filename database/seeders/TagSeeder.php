<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tags = [
            'Laravel',
            'PHP',
            'JavaScript',
            'Vue',
            'React',
            'Travel Tips',
            'Adventure',
            'Backpacking',
            'Recipes',
            'Cooking',
            'Restaurants',
            'Productivity',
            'Self Improvement',
            'Mindfulness',
            'Fitness',
            'Nutrition',
            'Mental Health'
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
                'slug' => Str::slug($tag)
            ]);
        }
    }
}
