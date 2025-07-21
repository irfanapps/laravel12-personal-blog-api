<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clear-inactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users inactive for over 1 year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = \App\Models\User::where('last_login_at', '<', now()->subYear())->delete();
        $this->info("Deleted {$deleted} inactive user(s).");
    }
}
