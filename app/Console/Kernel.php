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
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 1. Renovación diaria obligatoria del CUFD antes del inicio de jornada comercial
        $schedule->command('facturacion:renovar-cufd')
            ->dailyAt('00:01')
            ->appendOutputTo(storage_path('logs/siat_cufd.log'));

        // 2. Sincronización diaria de catálogos paramétricos del SIAT
        $schedule->command('facturacion:sincronizar-catalogos')
            ->dailyAt('04:00')
            ->appendOutputTo(storage_path('logs/siat_catalogos.log'));
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
