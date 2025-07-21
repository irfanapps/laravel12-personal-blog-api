<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:sanctum')->except(['index', 'show']);
    // }

    /**
     * Get all categories
     */
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * Create new category (Admin only)
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validatedData());
        return new CategoryResource($category);
    }

    /**
     * Get single category
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update category (Admin only)
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validatedData());
        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        try {
            // Check if category is already soft-deleted
            if ($category->trashed()) {
                return response()->json([
                    'message' => 'Category has already been deleted.'
                ], 410); // 410 Gone status for already deleted resources
            }

            // Remove category relations from posts
            $category->posts()->update(['category_id' => null]);

            // Soft delete the category
            $category->delete();
            //$category->forceDelete(); // Permanent deletion

            return response()->json([
                'success' => true,
                'message' => 'Category moved to trash. You can restore it within 30 days.',
                'data' => [
                    'deleted_at' => $category->deleted_at->format('Y-m-d H:i:s'),
                    'restore_deadline' => now()->addDays(30)->format('Y-m-d H:i:s')
                ],
            ], 200); // 200 OK for successful soft deletion

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category due to database error.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the category.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
