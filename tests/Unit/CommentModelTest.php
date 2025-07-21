<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function it_can_create_a_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()->create([
            'content' => 'Test comment',
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test comment', $comment->content);
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($post->id, $comment->post_id);
    }
}
