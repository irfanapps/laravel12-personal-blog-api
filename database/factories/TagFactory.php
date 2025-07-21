<?php

// database/factories/TagFactory.php
namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'slug' => $this->faker->unique()->slug,
            // 'user_id' => User::factory(),
            // 'category_id' => Category::factory(),
        ];
    }

    // State method for tags without category
    public function withoutCategory()
    {
        return $this->state(function (array $attributes) {
            return [
                'category_id' => null,
            ];
        });
    }

    // State method for specific user
    // public function forUser(User $user)
    // {
    //     return $this->state(function (array $attributes) use ($user) {
    //         return [
    //             'user_id' => $user->id,
    //         ];
    //     });
    // }

    // // State method for specific category
    // public function forCategory(Category $category)
    // {
    //     return $this->state(function (array $attributes) use ($category) {
    //         return [
    //             'category_id' => $category->id,
    //         ];
    //     });
    // }
}
