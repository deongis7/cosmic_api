<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
		        // $schedule->command('inspire')
        //          ->hourly();
		$filePath       = base_path().'/storage/logs/Parsing.log';

        // Parsing
        $schedule->call('\App\Http\Controllers\TmpPerimeterController@parsingPerimeter')->everyMinute()->sendOutputTo($filePath);
		
    }
}
