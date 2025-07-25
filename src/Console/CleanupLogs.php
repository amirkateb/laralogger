<?php

namespace Laralogger\Console;

use Illuminate\Console\Command;
use Laralogger\Models\ErrorLog;
use Carbon\Carbon;

class CleanupLogs extends Command
{
    protected $signature = 'laralog:cleanup {--days=30}';
    protected $description = 'Delete error logs older than a given number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $count = ErrorLog::where('created_at', '<', $cutoff)->count();

        ErrorLog::where('created_at', '<', $cutoff)->delete();

        $this->info("🧹 Deleted {$count} log(s) older than {$cutoff->toDateTimeString()}");

        return self::SUCCESS;
    }
}