<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\TagRequest;
use App\Http\Requests\MergeTagsRequest;

class TagController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum')->except(['index', 'show']);
    // }

    /**
     * Get all tags
     */
    public function index()
    {
        $tags = Tag::orderBy('name')->get();
        return TagResource::collection($tags);
    }

    /**
     * Create new tag (Admin only)
     */
    public function store(TagRequest $request)
    {
        $tag = Tag::create($request->validatedData());
        return new TagResource($tag);
    }


    /**
     * Get single tag
     */
    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    /**
     * Update tag (Admin only)
     */
    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->validatedData());
        return new TagResource($tag);
    }

    /**
     * Delete tag (Admin only)
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->noContent();
    }

    /**
     * Merge multiple tags into one (Admin only)
     */
    public function merge(Request $request)
    {
        $request->validate([
            'from_tags' => 'required|array',
            'to_tag' => 'required|string'
        ]);

        // Find or create target tag
        $targetTag = Tag::firstOrCreate(
            ['name' => $request->to_tag],
            ['slug' => Str::slug($request->to_tag)]
        );

        // Get tags to merge
        $tagsToMerge = Tag::whereIn('id', $request->from_tags)->get();

        // Update all posts with these tags
        foreach ($tagsToMerge as $tag) {
            $postIds = $tag->posts()->pluck('posts.id');
            $targetTag->posts()->syncWithoutDetaching($postIds);
            $tag->delete();
        }

        return new TagResource($targetTag);
    }
}
