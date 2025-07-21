<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible'
    ];

    protected $casts = [
        'is_visible' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Automatically generate slug from name
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    /**
     * Relasi ke Post (one-to-many)
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Scope untuk kategori yang visible
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Get route key name for URL (slug)
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Count posts in this category
     */
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }
}
