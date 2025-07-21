<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $categories = Category::all();
        //$tags = Tag::all();
        $yearMonth = now()->format('Y/m');

        foreach (range(1, 5) as $i) {
            $isDraft = $i % 10 === 0; // Every 10th post is a draft

            $post = Post::create([
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'title' => fake()->sentence(),
                'slug' => fake()->unique()->slug(),
                'content' => fake()->paragraphs(5, true),
                'excerpt' => fake()->paragraph(),
                'featured_image' => $i % 3 === 0 ? 'posts/' . $yearMonth . '/featured/default.jpg' : null,
                'is_draft' => $isDraft,
                'published_at' => $isDraft ? null : now()->subDays(rand(0, 30))
            ]);

            // Attach 1-5 random tags
            // $post->tags()->attach(
            //     $tags->random(rand(1, 5))->pluck('id')
            // );
        }
    }
}
