<?php

namespace Laralogger\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Laralogger\Models\ErrorLog;
use Laralogger\Services\NotificationManager;
use Laralogger\Services\AIAnalyzer;
use Illuminate\Support\Facades\File;

class ScanNginxLogs extends Command
{
    protected $signature = 'laralog:scan-nginx-log';
    protected $description = 'Scan Nginx error log and detect critical errors';

    public function handle(): int
    {
        $config = Config::get('laralogger.system_logs.nginx');

        if (!$config['enabled']) {
            $this->warn('Nginx log monitoring is disabled.');
            return self::SUCCESS;
        }

        $path = $config['path'];
        $pattern = $config['pattern'];

        if (!File::exists($path)) {
            $this->error("Nginx log file not found at: {$path}");
            return self::FAILURE;
        }

        $lines = collect(explode("\n", File::get($path)))
            ->reverse()
            ->filter(fn ($line) => preg_match($pattern, $line))
            ->take(3)
            ->reverse();

        foreach ($lines as $line) {
            $exists = ErrorLog::where('message', $line)->where('created_at', '>=', now()->subMinutes(10))->exists();
            if ($exists) {
                continue;
            }

            $log = ErrorLog::create([
                'status_code' => 502,
                'message' => $line,
                'url' => 'nginx',
                'method' => null,
                'ip' => null,
                'user_agent' => null,
                'headers' => null,
                'payload' => null,
                'user_id' => null,
                'user_name' => null,
                'guard' => 'system',
            ]);

            if ($config['send_notification']) {
                NotificationManager::notify($log);
            }

            if ($config['ai_analysis']) {
                AIAnalyzer::analyze($log);
            }

            $this->info("🚨 New Nginx error detected and logged.");
        }

        return self::SUCCESS;
    }
}