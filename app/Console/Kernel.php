<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use AiLeZai\Util\Lumen\CodeGen\Console\Commands\CodeGenerateCommand;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CodeGenerateCommand::class,
//        DemoCommand::class,
//        DemoMqCommand::class,
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
    }
}
