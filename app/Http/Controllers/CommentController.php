<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index()
    {
        // if have parameter post_id, filter comment as post
        if (request()->has('post_id')) {
            $comments = Comment::with([
                'user',
                'replies.user',
                'replies.replies.user'
            ])
                ->where('post_id', request('post_id'))
                ->whereNull('parent_id') // Only Main 
                ->latest()
                ->paginate(10);
        } else {
            // if no paremeter, show all comment (with pagination)
            $comments = Comment::with([
                'user',
                'replies.user',
                'replies.replies.user',
                'post'
            ])
                ->whereNull('parent_id') // only main comment
                ->latest()
                ->paginate(15);
        }

        return new CommentCollection($comments);
    }

    /**
     * Displays comment details along with replies
     */
    public function show($id)
    {
        // Search for comments including those that have been soft deleted
        $comment = Comment::withTrashed()
            ->with([
                'user',
                'replies.user',
                'replies.replies.user',
                'post'
            ])
            ->find($id);

        // If comment not found
        if (!$comment) {
            return response()->json([
                'message' => 'Komentar tidak ditemukan',
                'errors' => [
                    'id' => ['Komentar dengan ID tersebut tidak ditemukan']
                ]
            ], 404);
        }

        return new CommentResource($comment);
    }

    public function store(StoreCommentRequest $request)
    {
        // Ensure authenticated user's ID is used
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        // Create comment with validated data
        $comment = Comment::create($validated);

        // Notify post owner if it's not their own comment
        if ($comment->post->user_id !== Auth::id()) {
            $comment->post->user->notify(new NewCommentNotification($comment));
        }

        return new CommentResource($comment->load(['user', 'replies', 'post']));
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        //Log::info(json_encode(Auth::id(), JSON_PRETTY_PRINT));

        // Check if the authenticated user is the comment owner
        if (Auth::id() !== $comment->user_id) {
            return response()->json([
                'message' => 'Unauthorized - You can only update your own comments',
                'errors' => [
                    'authorization' => ['You are not authorized to update this comment']
                ]
            ], 403);
        }

        // Update the comment content
        $comment->update([
            'content' => $request->content,
            'updated_at' => now() // Explicitly set update time
        ]);

        // Return the updated comment with fresh data
        return new CommentResource($comment->fresh()->load(['user', 'replies']));
    }


    // Menyimpan reply
    public function reply(StoreCommentRequest $request, Comment $comment)
    {
        $reply = $comment->replies()->create([
            'content' => $request->content,
            'user_id' => Auth::id(), // Use Auth facade instead of auth() helper
            'post_id' => $comment->post_id
        ]);

        return response()->json([
            'message' => 'Balasan berhasil ditambahkan',
            'reply' => $reply->load('user')
        ], 201);
    }

    /**
     * Like/unlike a comment
     */
    public function like(Comment $comment)
    {
        $user = Auth::user();

        if ($comment->isLikedBy($user)) {
            $comment->unlike($user);
            $message = 'Comment unliked successfully';
            $isLiked = false;
        } else {
            $comment->like($user);
            $message = 'Comment liked successfully';
            $isLiked = true;
        }

        return response()->json([
            'message' => $message,
            'likes_count' => $comment->likes()->count(),
            'is_liked' => $isLiked
        ]);
    }

    // Delete comment (soft delete)
    public function destroy(Comment $comment)
    {
        //$this->authorize('delete', $comment);
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment success deleted',
            'deleted_at' => $comment->deleted_at->format('Y-m-d H:i:s')
        ]);
    }
}
