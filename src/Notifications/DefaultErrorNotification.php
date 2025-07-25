<?php

namespace Laralogger\Notifications;

use Laralogger\Models\ErrorLog;
use Illuminate\Notifications\Messages\MailMessage;

class DefaultErrorNotification
{
    protected ErrorLog $log;

    public function __construct(ErrorLog $log)
    {
        $this->log = $log;
    }

    public function toTelegram(): array
    {
        $log = $this->log;

        $text = "🚨 *خطای {$log->status_code} در {$log->method}*\n"
            . "🔗 `{$log->url}`\n"
            . "📱 IP: `{$log->ip}`\n"
            . ($log->user_name ? "👤 User: {$log->user_name}\n" : '')
            . "🧾 *Message:* `" . addslashes(substr($log->message, 0, 300)) . "`\n"
            . "🕒 " . $log->created_at->format('Y-m-d H:i:s');

        return [
            'chat_id' => config('services.telegram.error_chat_id'),
            'token' => config('services.telegram.bot_token'),
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
    }

    public function toMail(): MailMessage
    {
        $log = $this->log;

        return (new MailMessage)
            ->subject("🚨 خطای {$log->status_code} در {$log->method}")
            ->line("URL: {$log->url}")
            ->line("IP: {$log->ip}")
            ->line("User: {$log->user_name} (ID: {$log->user_id})")
            ->line("Message: {$log->message}")
            ->line("زمان: {$log->created_at}")
            ->line(" ")
            ->line("اگر این خطا ادامه‌دار بود، لطفاً بررسی شود.");
    }
}