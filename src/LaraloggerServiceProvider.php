<?php

namespace Laralogger;

use Illuminate\Support\ServiceProvider;

class LaraloggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laralogger.php', 'laralogger');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laralogger.php' => config_path('laralogger.php'),
        ], 'laralogger-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/console.php');

            $this->commands([
                \Laralogger\Console\CleanupLogs::class,
                \Laralogger\Console\TestErrorCommand::class,
                \Laralogger\Console\ExportLogs::class,
                \Laralogger\Console\ScanNginxLogs::class,
            ]);
        }
    }
}