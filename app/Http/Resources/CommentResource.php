<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->when(
                $this->deleted_at === null,
                $this->content,
                'This comment has been deleted'
            ),
            'is_deleted' => $this->deleted_at !== null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // User who made the comment
            'author' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar_url,
                //'profile_url' => route('users.show', $this->user->id)
            ]),

            // Replies to this comment (nested)
            'replies' => $this->whenLoaded('replies', function () {
                return $this->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->deleted_at ? 'This reply has been deleted' : $reply->content,
                        'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                        'author' => $reply->user ? [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'avatar' => $reply->user->avatar_url
                        ] : null,
                        // Second level replies
                        'replies' => $reply->replies ? $reply->replies->map(function ($nestedReply) {
                            return [
                                'id' => $nestedReply->id,
                                'content' => $nestedReply->deleted_at ? 'This reply has been deleted' : $nestedReply->content,
                                'created_at' => $nestedReply->created_at->format('Y-m-d H:i:s'),
                                'author' => $nestedReply->user ? [
                                    'id' => $nestedReply->user->id,
                                    'name' => $nestedReply->user->name
                                ] : null
                            ];
                        }) : []
                    ];
                });
            }),

            // Interaction data
            'likes_count' => $this->whenCounted('likes'),
            'is_liked' => $this->when(
                $request->user() && $this->relationLoaded('likes'),
                fn() => $this->likes->contains('user_id', $request->user()->id)
            ),

            // Action links
            // 'links' => [
            //     'self' => route('comments.show', $this->id),
            //     'reply' => route('comments.reply', $this->id),
            //     'like' => route('comments.like', $this->id),
            // ]
        ];
    }

    /**
     * Add additional meta data to the resource response.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'response_time' => now()->toDateTimeString(),
                'depth_limit' => 3,
                'character_limit' => 1000
            ]
        ];
    }
}
