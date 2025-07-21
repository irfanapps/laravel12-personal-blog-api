<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PersonalAccessToken;
use Carbon\Carbon;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';
    protected $description = 'Remove expired Sanctum tokens';

    public function handle()
    {
        $deleted = PersonalAccessToken::where('expired_at', '<=', Carbon::now())
            ->orWhere(function ($query) {
                $query->whereNull('expired_at')
                    ->where('created_at', '<=', Carbon::now()->subDays(7)); // Delete tokens with no expiry older than 7 days
            })
            ->delete();

        $this->info("Deleted {$deleted} expired tokens.");
        return 0;
    }
}
