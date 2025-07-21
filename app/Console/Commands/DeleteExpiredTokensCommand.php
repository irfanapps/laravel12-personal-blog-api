<?php

namespace App\Console\Commands;

use App\Models\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredTokensCommand extends Command
{
    protected $signature = 'tokens:delete-expired';
    protected $description = 'Delete expired Sanctum tokens';

    public function handle()
    {
        $deleted = PersonalAccessToken::where('expires_at', '<=', Carbon::now())->delete();

        $this->info("Deleted {$deleted} expired tokens.");

        return 0;
    }
}
