<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->when(
                $request->routeIs('posts.show'),
                $this->content
            ),
            'excerpt' => $this->excerpt,
            'featured_image_url' => $this->featured_image_url,
            'is_draft' => $this->is_draft,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug
            ]),
            'tags' => $this->whenLoaded(
                'tags',
                fn() =>
                $this->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug
                ])
            ),
            'author' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user->avatar_url
            ]),
            'comments_count' => $this->whenCounted('comments'),
            'links' => [
                'self' => route('posts.show', $this->slug),
                'edit' => route('posts.update', $this->slug),
                'delete' => route('posts.destroy', $this->slug)
            ]
        ];
    }

    public function with($request)
    {
        return [
            'meta' => [
                'version' => '1.0',
                'api_version' => 'v1'
            ]
        ];
    }
}
