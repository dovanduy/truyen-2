<?php

namespace App\Console;

use App\Console\Commands\BulkSMSFile;
use App\Console\Commands\SendRecurringInvoice;
use App\Console\Commands\SendScheduleSMS;
use App\Console\Commands\VerifyProductStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CrawFirst;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // SendScheduleSMS::class,
        // SendRecurringInvoice::class,
        //VerifyProductStatus::class,
        // BulkSMSFile::class,
        CrawFirst::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('sms:schedule')->everyMinute();
        //$schedule->command('sms:sendbulk')->everyMinute();
        //$schedule->command('invoice:recurring')->dailyAt('12:01');
        //$schedule->command('VerifyProductStatus:verify')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
