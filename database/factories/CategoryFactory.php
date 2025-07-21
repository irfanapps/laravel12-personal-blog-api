<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->words($this->faker->numberBetween(1, 3), true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->boolean(70) ? $this->faker->paragraph() : null,
            'is_visible' => $this->faker->boolean(90),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * State for visible categories
     */
    public function visible()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => true,
            ];
        });
    }

    /**
     * State for hidden categories
     */
    public function hidden()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => false,
            ];
        });
    }

    /**
     * State with specific name and auto-generated slug
     */
    public function withName(string $name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
                'slug' => Str::slug($name),
            ];
        });
    }

    /**
     * State with long description
     */
    public function withLongDescription()
    {
        return $this->state(function (array $attributes) {
            return [
                'description' => $this->faker->paragraphs(3, true),
            ];
        });
    }
}
