<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\CheckTokenExpiration;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:custom-limit');

    // Public post routes can be added here if needed
    // Route::get('/posts', [PostController::class, 'publicIndex']);
});

// Authenticated routes
//Route::middleware('auth:sanctum')->group(function () {
Route::middleware(['auth:sanctum', 'token.expiration'])->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/me', [AuthController::class, 'update']);
    });

    // Post routes
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/drafts', [PostController::class, 'drafts']);
        Route::post('/', [PostController::class, 'store']);

        // Slug-based routes
        Route::prefix('{post:slug}')->group(function () {
            Route::get('/', [PostController::class, 'show'])->name('posts.show');
            Route::put('/', [PostController::class, 'update'])->name('posts.update');
            Route::delete('/', [PostController::class, 'destroy'])->name('posts.destroy');
            Route::post('/publish', [PostController::class, 'publish']);
            // Route::post('/comments', [CommentController::class, 'store']);
        });
    });

    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);

        Route::prefix('{category:slug}')->group(function () {
            Route::put('/', [CategoryController::class, 'update']);
            Route::delete('/', [CategoryController::class, 'destroy']);
            // In your Restore & Force
            // Route::post('/categories/{category}/restore', [CategoryController::class, 'restore'])
            //     ->name('categories.restore')
            //     ->withTrashed();

            // Route::delete('/categories/{category}/force', [CategoryController::class, 'forceDelete'])
            //     ->name('categories.force-delete')
            //     ->withTrashed();
        });
    });

    Route::prefix('tags')->group(function () {
        Route::post('/', [TagController::class, 'store']);
        Route::post('/merge', [TagController::class, 'merge']);

        Route::prefix('{tag:slug}')->group(function () {
            Route::put('/', [TagController::class, 'update']);
            Route::delete('/', [TagController::class, 'destroy']);
        });
    });

    // Comment routes
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
        Route::post('/{comment}/like', [CommentController::class, 'like']);
        Route::post('/{comment}/reply', [CommentController::class, 'reply']);
    });
});

// Public routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('{category:slug}', [CategoryController::class, 'show']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('{tag:slug}', [TagController::class, 'show']);
});

Route::prefix('comments')->group(function () {
    Route::get('/', [CommentController::class, 'index']);
    Route::get('/{comment}', [CommentController::class, 'show']);
});
