<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DeleteExpiredTokensCommand;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('tokens:delete-expired', function () {
    $this->call(DeleteExpiredTokensCommand::class);
})->describe('Token Deleted');


// Schedule::command('users:send-inactive')
//     ->daily()
//     ->withoutOverlapping();


// Schedule::command('tokens:delete-expired')
//     ->dailyAt('13:55');
//     //->withoutOverlapping();
//     // ->onOneServer() // Untuk aplikasi multi-server
//     // ->appendOutputTo(storage_path('logs/token-cleaner.log'));
