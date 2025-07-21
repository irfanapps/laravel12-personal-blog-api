<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Carbon\Carbon;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isValid()
    {
        return !$this->expired_at || $this->expired_at->isFuture();
    }

    public function scopeValid($query)
    {
        return $query->whereNull('expired_at')
            ->orWhere('expired_at', '>', now());
    }
}
