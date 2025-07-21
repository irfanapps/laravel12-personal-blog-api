<?php

namespace Database\Seeders;

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // Take some post with already users
        $posts = Post::take(20)->get();
        $users = User::take(20)->get();

        // Make 50 Random Comment
        Comment::factory(50)->create([
            'post_id' => function () use ($posts) {
                return $posts->random()->id;
            },
            'user_id' => function () use ($users) {
                return $users->random()->id;
            }
        ]);

        // or use factory without special condition
        // Comment::factory(10)->create();
    }
}
