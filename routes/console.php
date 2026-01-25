<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('maintenance:down {--secret= : Secret phrase to bypass maintenance mode} {--refresh=15 : Browser refresh seconds} {--retry=60 : Retry-After seconds}', function () {
    $secret = (string) ($this->option('secret') ?: env('MAINTENANCE_SECRET', ''));

    if (trim($secret) === '') {
        $this->error('MAINTENANCE_SECRET is not set. Add it to your .env first.');
        $this->line('Example: MAINTENANCE_SECRET=situba-bypass');
        return 1;
    }

    $appUrl = rtrim((string) config('app.url', env('APP_URL', '')), '/');

    Artisan::call('down', [
        '--secret' => $secret,
        '--refresh' => (int) $this->option('refresh'),
        '--retry' => (int) $this->option('retry'),
    ]);

    $this->info('Application is now in maintenance mode.');

    if ($appUrl !== '') {
        $this->line("Bypass URL: {$appUrl}/{$secret}");
        $this->line('Open it once to get the bypass cookie.');
    } else {
        $this->line("Bypass secret: {$secret}");
    }

    return 0;
})->purpose('Put the app into maintenance mode using MAINTENANCE_SECRET');

Artisan::command('maintenance:up', function () {
    Artisan::call('up');
    $this->info('Application is live (maintenance mode disabled).');
    return 0;
})->purpose('Disable maintenance mode');
