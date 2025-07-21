<?php

namespace Tests\Unit;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function it_can_create_a_tag()
    {
        $tag = Tag::factory()->create([
            'name' => 'Test Tag',
            'slug' => 'test-tag'
        ]);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('Test Tag', $tag->name);
        $this->assertEquals('test-tag', $tag->slug);
    }

    /** @test */
    public function it_generates_slug_automatically()
    {
        $tag = Tag::factory()->create([
            'name' => 'Test Tag'
        ]);

        $this->assertEquals('test-tag', $tag->slug);
    }
}
