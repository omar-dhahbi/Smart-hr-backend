<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\AuthController;


class Kernel extends ConsoleKernel
{

     protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            app(AuthController::class)->fermerSession();
        })->dailyAt('17:00');
    }
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

