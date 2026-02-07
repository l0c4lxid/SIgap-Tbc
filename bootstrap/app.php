<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\DispatchWhatsAppMessages;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        DispatchWhatsAppMessages::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Auto-dispatch WhatsApp messages every minute; allow during maintenance and prevent overlaps
        $schedule->command('wa:dispatch')
            ->everyMinute()
            ->withoutOverlapping()
            ->evenInMaintenanceMode();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
