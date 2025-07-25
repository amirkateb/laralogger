<?php

namespace Laralogger\Console;

use Illuminate\Console\Command;
use Laralogger\Services\ErrorLogger;
use Laralogger\Services\NotificationManager;
use Laralogger\Services\AIAnalyzer;
use Illuminate\Http\Request;

class TestErrorCommand extends Command
{
    protected $signature = 'laralog:test {--code=500}';
    protected $description = 'Fully test the logger system with a simulated error';

    public function handle(): int
    {
        $code = (int) $this->option('code');

        $exception = match ($code) {
            403 => new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Access denied for testing.'),
            404 => new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Page not found.'),
            422 => new \Illuminate\Validation\ValidationException(validator: validator([], ['field' => 'required'])),
            default => new \Exception('Simulated server error (500).')
        };

        $request = Request::create('/test/exception', 'GET');

        $log = ErrorLogger::log($request, $exception, $code);

        NotificationManager::notify($log);
        AIAnalyzer::analyze($log);

        $this->info("✅ Simulated error {$code} has been logged and processed.");

        return self::SUCCESS;
    }
}