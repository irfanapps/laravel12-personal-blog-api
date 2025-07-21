<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = "id";
    protected $keyType = "int";
    protected $table = "comments";

    protected $fillable = [
        'content',
        'user_id',
        'post_id'
    ];

    //protected $with = ['user', 'replies'];
    //protected $withCount = ['likes', 'replies'];

    /**
     * Relasi ke User (pemilik komentar)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Relasi ke parent comment (untuk reply)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Relasi ke replies (child comments)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Relasi many-to-many ke User (likes)
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_likes')
            ->withTimestamps();
    }

    /**
     * Cek apakah user tertentu sudah like komentar ini
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function like(User $user)
    {
        $this->likes()->syncWithoutDetaching([$user->id]);
    }

    public function unlike(User $user)
    {
        $this->likes()->detach($user->id);
    }

    /**
     * Scope untuk komentar parent (bukan reply)
     */
    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk komentar dengan like terbanyak
     */
    public function scopeMostLiked($query)
    {
        return $query->withCount('likes')
            ->orderByDesc('likes_count');
    }

    /**
     * Format tanggal created_at
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Hitung total like
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Cek apakah current user sudah like
     */
    public function getIsLikedAttribute()
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->isLikedBy(Auth::user());
    }
}
