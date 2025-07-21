<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    //use RefreshDatabase;

    protected $user;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->post = Post::factory()->create();
    }


    /** @test */
    public function can_get_all_comments()
    {
        Comment::factory()->count(3)->create(['post_id' => $this->post->id]);

        $response = $this->getJson('/api/comments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_get_single_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $comment->id,
                    'content' => $comment->content
                ]
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_own_comment()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Updated comment content'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'content' => 'Updated comment content'
                ]
            ]);

        $this->assertDatabaseHas('comments', ['content' => 'Updated comment content']);
    }

    /** @test */
    public function user_cannot_update_other_users_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Updated comment content'
            ]);

        $response->assertStatus(403);
    }
}
