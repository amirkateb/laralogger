<?php

namespace Laralogger\Services;

use Illuminate\Http\Request;
use Throwable;
use Laralogger\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ErrorLogger
{
    public static function log(Request $request, Throwable $exception, int $statusCode = null): ErrorLog
    {
        $user = Auth::user();
        $guard = optional(Auth::guard())->getName();

        return ErrorLog::create([
            'status_code' => $statusCode,
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'trace' => config('laralogger.include_trace') ? $exception->getTraceAsString() : null,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => config('laralogger.include_headers') ? $request->headers->all() : null,
            'payload' => config('laralogger.include_payload') ? $request->all() : null,
            'user_id' => $user?->getAuthIdentifier(),
            'user_name' => $user?->name ?? null,
            'guard' => config('laralogger.guard_detection') ? $guard : null,
        ]);
    }
}