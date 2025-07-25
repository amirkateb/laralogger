<?php

namespace Laralogger\Services;

use Laralogger\Models\ErrorLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class AIAnalyzer
{
    public static function analyze(ErrorLog $log): void
    {
        if (!Config::get('laralogger.ai.enabled')) {
            return;
        }

        $apiKey = Config::get('laralogger.ai.secret');
        $model = Config::get('laralogger.ai.model', 'gpt-4');
        $prompt = Config::get('laralogger.ai.prompt', 'خطای زیر را بررسی کن و دلیل و راه‌حل احتمالی آن را بگو:');

        $input = $prompt . "\n\n"
            . "کد وضعیت: {$log->status_code}\n"
            . "پیغام: {$log->message}\n"
            . "آدرس: {$log->url}\n"
            . "متد: {$log->method}\n"
            . "کلاس خطا: {$log->exception_class}\n";

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'شما یک متخصص تحلیل خطا هستید.'],
                    ['role' => 'user', 'content' => $input]
                ],
                'temperature' => 0.3,
            ]);

        if ($response->successful()) {
            $result = $response->json('choices.0.message.content');
            $log->update(['ai_analysis' => $result]);
        }
    }
}