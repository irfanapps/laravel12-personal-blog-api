<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function it_can_determine_if_post_is_draft()
    {
        $post = Post::factory()->create(['is_draft' => true]);
        //dump($post->draft); // Check if it's true
        $this->assertFalse($post->isDraft());
    }
}
