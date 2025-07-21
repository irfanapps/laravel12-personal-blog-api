<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'content' => $this->faker->paragraphs(5, true),
            'excerpt' => $this->faker->paragraph(),
            'featured_image' => $this->faker->optional()->imageUrl(),
            'is_draft' => $this->faker->boolean(20), // 20% chance of being draft
            'published_at' => $this->faker->optional(80)->dateTimeBetween('-1 year', 'now'), // 80% chance of having published date
        ];
    }

    /**
     * State for published posts
     */
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_draft' => false,
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    /**
     * State for draft posts
     */
    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_draft' => true,
                'published_at' => null,
            ];
        });
    }

    /**
     * State with specific user
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }

    /**
     * State with specific category
     */
    public function forCategory(Category $category)
    {
        return $this->state(function (array $attributes) use ($category) {
            return [
                'category_id' => $category->id,
            ];
        });
    }
}
