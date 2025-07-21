<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    /**
     * Relasi many-to-many ke Post
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Automatically generate slug from name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            $tag->slug = Str::slug($tag->name);
        });

        static::updating(function ($tag) {
            $tag->slug = Str::slug($tag->name);
        });
    }

    /**
     * Get route key name for URL (slug)
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Count posts with this tag
     */
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    /**
     * Scope untuk popular tags
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit($limit);
    }
}
