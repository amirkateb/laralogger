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
    protected $description = 'تست کامل سیستم لاگ‌گیری با یک خطای شبیه‌سازی‌شده';

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

        $this->info("✅ خطای تستی {$code} ثبت و پردازش شد.");

        return self::SUCCESS;
    }
}