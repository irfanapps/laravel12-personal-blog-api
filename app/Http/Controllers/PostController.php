<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{

    public function index(Request $request): PostCollection
    {
        $user = Auth::user();

        // Start building the query with all necessary relationships
        $query = Post::with([
            'user',
            'category',
            'tags', // Include tags relationship
            'comments' => function ($query) {
                $query->with('user') // Eager load comment authors
                    ->orderBy('created_at', 'desc')
                    ->limit(5); // Limit to 5 recent comments per post
            }
        ])
            ->withCount('comments')
            ->where('user_id', $user->id)  // Only show posts belonging to logged-in user
            ->orderBy('created_at', 'desc');

        // Filter draft posts for non-admin users
        if (!$user->is_admin) {
            $query->where('is_draft', false)
                ->whereNotNull('published_at');
        }

        // Filter by category slug if provided
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by user ID if provided
        if ($request->has('user')) {
            // Non-admin can only filter by their own posts
            $query->where('user_id', $user->is_admin ? $request->user : $user->id);
        }

        // Filter by tag if provided
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Search in title, content, and tags if search term provided
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%')
                    ->orWhereHas('tags', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Additional filters
        if ($request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->content) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }

        // Count comments for each post (optional)
        if ($request->has('with_comment_count')) {
            $query->withCount('comments');
        }

        // Filter by minimum comments (optional)
        if ($request->has('min_comments')) {
            $query->has('comments', '>=', $request->min_comments);
        }

        $perPage = $request->per_page ?? 15;
        $posts = $query->paginate($perPage);
        //Log::info(json_encode($posts, JSON_PRETTY_PRINT));

        return new PostCollection($posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * 
     */
    public function store(PostRequest $request)
    {
        // Get validated data
        $validatedData = $request->validatedData();

        // Generate slug from title
        $slug = Str::slug($validatedData['title']);

        // Check if the slug already exists and create a unique one.
        $count = Post::where('slug', 'LIKE', "{$slug}%")->count();
        $validatedData['slug'] = $count ? "{$slug}-{$count}" : $slug;

        // Add user_id of currently logged in user
        $validatedData['user_id'] = Auth::user()->id;

        // Save data post
        $post = Post::create($validatedData);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = collect($request->tags)->map(function ($tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName], ['slug' => Str::slug($tagName)]);
                return $tag->id;
            });

            $post->tags()->sync($tagIds);
        }

        // Handle featured image
        // if ($request->hasFile('featured_image')) {
        //     $path = $request->file('featured_image')->store('posts/featured_images', 'public');
        //     $post->update(['featured_image' => $path]);
        // }

        // Featured image handle - CHANGED
        if ($request->hasFile('featured_image')) {
            $yearMonth = now()->format('Y/m');
            $filename = Str::random(20) . '.' . $request->file('featured_image')->extension();

            $path = $request->file('featured_image')->storeAs(
                "posts/{$yearMonth}/featured_images",
                $filename,
                'public' // Disk public
            );

            $post->update(['featured_image' => $path]);
        }

        return new PostResource($post->load(['category', 'tags', 'user']));
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(Post $post)
    {
        //$user = Auth::user();

        $post->load(['category', 'user']);
        //$post->load(['category', 'tags', 'user', 'comments.user']);

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(PostRequest $request, Post $post)
    {
        // Only the owner or admin can update
        if ($post->user_id != Auth::user()->id && !Auth::user()->is_admin) {
            abort(403, 'You are not authorized to update this post');
        }

        $data = $request->validatedData();

        // Update post
        $post->update($data);

        // Update tags if have
        if ($request->has('tags')) {
            $tagIds = collect($request->tags)->map(function ($tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName], ['slug' => Str::slug($tagName)]);
                return $tag->id;
            });

            $post->tags()->sync($tagIds);
        }

        // Update featured image if have
        if ($request->hasFile('featured_image')) {
            // Delete old images if any
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $path = $request->file('featured_image')->store('posts/featured_images', 'public');
            $post->update(['featured_image' => $path]);
        }

        return new PostResource($post->fresh()->load(['category', 'tags', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(Post $post)
    {
        try {
            // Authorization check - only owner or admin can delete
            if ($post->user_id != Auth::id() && !Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this post'
                ], 403);
            }

            // Begin transaction for atomic operations
            DB::beginTransaction();

            // Delete featured image if exists
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Delete the post
            $deleted = $post->delete();

            if (!$deleted) {
                throw new \Exception('Failed to delete post');
            }

            // Commit transaction if all operations succeeded
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
                // 'data' => [
                //     'post_id' => $post->id,
                //     'deleted_at' => now()->toDateTimeString()
                // ]
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function publish(Post $post)
    {
        // Only owner or admin can publish
        if ($post->user_id != Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to publish this post'
            ], 403);
        }

        // Check if already published
        if (!$post->is_draft) {
            return response()->json([
                'success' => false,
                'message' => 'Post is already published'
            ], 400);
        }

        $post->update([
            'is_draft' => false,
            'published_at' => now()
        ]);

        Log::info(json_encode($post, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully',
            //'data' => new PostResource($post->fresh()->load(['category', 'tags', 'user']))
            'data' => new PostResource($post->fresh()->load(['category', 'user']))

        ]);
    }

    public function drafts(Request $request)
    {
        $query = Post::with(['user:id,name', 'category:id,name'])
            ->where('is_draft', true)
            ->orderBy('created_at', 'desc');

        // For non-admin users, only show their own drafts
        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        // Optional filters
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->per_page ?? 15;
        $drafts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($drafts),
            'meta' => [
                'current_page' => $drafts->currentPage(),
                'total' => $drafts->total(),
                'per_page' => $drafts->perPage(),
            ]
        ]);
    }
}
