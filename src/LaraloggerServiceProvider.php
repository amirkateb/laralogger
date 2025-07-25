<?php

namespace Laralogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Queue;
use Throwable;

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

        if (
            Config::get('laralogger.active') &&
            in_array(App::environment(), Config::get('laralogger.environments', []))
        ) {
            App::reportable(function (Throwable $e) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $logCodes = Config::get('laralogger.log_status_codes', []);

                if (!in_array($statusCode, $logCodes)) {
                    return;
                }

                $log = \Laralogger\Services\ErrorLogger::log(Request::instance(), $e, $statusCode);

                Queue::push(function () use ($log) {
                    \Laralogger\Services\NotificationManager::notify($log);
                    \Laralogger\Services\AIAnalyzer::analyze($log);
                }, [], Config::get('laralogger.notifications.queue.name', 'default'));
            });
        }
    }
}