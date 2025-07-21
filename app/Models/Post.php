<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'is_draft',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_draft' => 'boolean'
    ];

    protected $appends = ['featured_image_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->slug = Str::slug($post->title);

            // Jika slug sudah ada, tambahkan timestamp
            if (static::where('slug', $post->slug)->exists()) {
                $post->slug = "{$post->slug}-" . now()->format('Ymd-His');
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $post->slug = Str::slug($post->title);

                // Jika slug baru sudah ada, tambahkan timestamp
                if (static::where('slug', $post->slug)
                    ->where('id', '!=', $post->id)
                    ->exists()
                ) {
                    $post->slug = "{$post->slug}-" . now()->format('Ymd-His');
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function commentCount()
    {
        return $this->allComments()->count();
    }

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/posts/featured_images/' . $this->featured_image);
        }
        return null;
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('is_draft', false)
            ->whereNotNull('published_at');
    }

    /**
     * Scope for draft posts
     */
    public function scopeDraft($query)
    {
        return $query->where('is_draft', true)
            ->orWhereNull('published_at');
    }

    // In App\Models\Post
    public function isDraft(): bool
    {
        return $this->draft === true; // or `return (bool) $this->draft;`
    }

    // public function scopeSearch($query, $term)
    // {
    //     return $query->where(function ($q) use ($term) {
    //         $q->where('title', 'like', "%$term%")
    //             ->orWhere('content', 'like', "%$term%");
    //     });
    // }

    // Scope untuk pencarian Full text
    // public function scopeSearch($query, $term)
    // {
    //     return $query->where(function ($q) use ($term) {
    //         // Full-text search jika didukung
    //         if (config('database.default') === 'mysql') {
    //             $q->whereRaw("MATCH(title,content) AGAINST(? IN BOOLEAN MODE)", [$term])
    //                 ->orWhereRaw("MATCH(title,content) AGAINST(? IN NATURAL LANGUAGE MODE)", [$term]);
    //         } else {
    //             // Fallback ke LIKE biasa untuk database lain
    //             $q->where('title', 'like', "%$term%")
    //                 ->orWhere('content', 'like', "%$term%");
    //         }
    //     });
    // }
}
