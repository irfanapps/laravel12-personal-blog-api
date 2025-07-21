<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }

    protected $commands = [
        Commands\ClearInactiveUsers::class,
        Commands\DeleteExpiredTokensCommand::class,
        Commands\CleanExpiredTokens::class,
    ];


    protected function schedule(Schedule $schedule): void
    {
        // Clean tokens every day at 23:00
        $schedule->command('tokens:delete-expired')
            ->dailyAt('23:55');
        // ->onOneServer() // For multi-server applications
        // ->appendOutputTo(storage_path('logs/token-cleaner.log'));


        // $schedule->command('tokens:clean')
        //     ->daily()
        //     ->between('2:00', '5:00'); // Only runs between 2-5 am
    }
}
