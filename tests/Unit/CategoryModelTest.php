<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function it_can_create_a_category()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
    }

    /** @test */
    public function it_generates_slug_automatically()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);

        $this->assertEquals('test-category', $category->slug);
    }
}
