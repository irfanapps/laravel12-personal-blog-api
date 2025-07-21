<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    //use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_create_category()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/categories', [
                'name' => 'Test Category',
                'slug' => 'test-category',
                'description' => 'Test description'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description']
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_category()
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function can_get_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_get_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/categories/{$category->slug}", [
                'name' => 'Updated Category',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Category'
                ]
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Updated Category']);
    }
}
