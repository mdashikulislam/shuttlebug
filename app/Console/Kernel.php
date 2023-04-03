<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     * production server cron fires every hour
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
         * debtors send month-end invoices and statements
         * run at noon on 28th of every month
         */
        $schedule->command('debtors:monthend')
            ->monthlyOn(28, '12:00');

        /*
         * debtors follow up outstanding
         * run at 6am on the 7th of every month
         */
        $schedule->command('debtors:followup')
            ->monthlyOn(7, '06:00');

        /**
         * monthly table clean up
         * run at 5am on 5th of every month
         */
        $schedule->command('table:cleanup')
            ->monthlyOn(5, '05:00');

        /**
         * year end move data to archives
         * run at 3am on 4th Jan
         */
        $schedule->command('yearend:archive')
            ->cron('* 8 4 1 *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
