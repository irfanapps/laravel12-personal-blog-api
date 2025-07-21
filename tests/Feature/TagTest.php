<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    //use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_create_tag()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/tags', [
                'name' => 'Test Tag',
                'slug' => 'test-tag'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug']
            ]);

        $this->assertDatabaseHas('tags', ['name' => 'Test Tag']);
    }

    /** @test */
    public function can_get_all_tags()
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_get_single_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/tags/{$tag->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tag->id,
                    'name' => $tag->name
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/tags/{$tag->slug}", [
                'name' => 'Updated Tag'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Tag'
                ]
            ]);

        $this->assertDatabaseHas('tags', ['name' => 'Updated Tag']);
    }

    /** @test */
    public function authenticated_user_can_delete_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tags/{$tag->slug}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
