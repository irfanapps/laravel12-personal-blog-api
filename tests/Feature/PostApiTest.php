<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class PostApiTest extends TestCase
{

    protected User $user;
    protected Post $post;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_post()
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test content'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_post()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'In this article we will learn how to create an API with Laravel with a connection to the frontend...',
            'is_draft' => true
        ], ['Authorization' => 'bearer test'])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Test Post',
                    'is_draft' => true
                ]
            ]);
    }

    /** @test */
    public function user_can_only_delete_own_posts()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2, ['*']);

        $response = $this->deleteJson("/api/posts/{$post->slug}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_update_other_users_posts()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/posts/{$this->post->slug}", [
            'title' => 'Attempted Update',
            'content' => 'In this article we will learn how to create an API with Laravel with a connection to the frontend...',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_post()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/posts/{$this->post->slug}", [
            'title' => 'Admin Updated Title',
            'content' => 'In this article we will learn how to create an API with Laravel with a connection to the frontend...',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Admin Updated Title'
                ]
            ]);
    }

    /** @test */
    public function user_can_search_posts()
    {
        Post::factory()->create([
            'title' => 'Laravel API Tutorial',
            'content' => 'In this article we will learn how to create an API with Laravel with a connection to the frontend...',
            'user_id' => $this->user->id
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/posts?search=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['title' => 'Laravel API Tutorial']
                ]
            ]);
    }

    /** @test */
    public function user_can_publish_draft_post()
    {
        $draft = Post::factory()->create([
            'user_id' => $this->user->id,
            'is_draft' => true,
            'published_at' => null
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/posts/{$draft->slug}/publish");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_draft' => false,
                    'published_at' => now()
                ]
            ]);
    }

    /** @test */
    public function cannot_publish_already_published_post()
    {
        $publishedPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'is_draft' => false,
            'published_at' => now()
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/posts/{$publishedPost->slug}/publish");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Post is already published'
            ]);
    }

    /** @test */
    public function user_can_view_own_drafts()
    {
        Post::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_draft' => true
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/posts/drafts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'is_draft']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }

    /** @test */
    public function admin_can_view_all_drafts()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Post::factory()->create([
            'user_id' => $this->user->id,
            'is_draft' => true
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/posts/drafts');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function user_can_delete_own_post()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/posts/{$this->post->slug}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($this->post);
    }

    /** @test */
    public function admin_can_delete_any_post()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/posts/{$this->post->slug}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($this->post);
    }
}
