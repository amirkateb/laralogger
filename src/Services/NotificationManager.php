<?php

namespace Laralogger\Services;

use Laralogger\Models\ErrorLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationManager
{
    public static function notify(ErrorLog $log): void
    {
        if (!Config::get('laralogger.notifications.enabled')) {
            return;
        }

        $channels = Config::get('laralogger.notifications.channels', []);
        $notifier = Config::get('laralogger.notifications.notifier');

        if ($notifier && class_exists($notifier)) {
            $notification = new $notifier($log);
        } else {
            $notification = new \Laralogger\Notifications\DefaultErrorNotification($log);
        }

        foreach ($channels as $channel) {
            match ($channel) {
                'telegram' => self::sendTelegram($notification),
                'email' => self::sendEmail($notification),
                default => null,
            };
        }
    }

    protected static function sendTelegram($notification): void
    {
        if (method_exists($notification, 'toTelegram')) {
            $data = $notification->toTelegram();
            file_get_contents("https://api.telegram.org/bot{$data['token']}/sendMessage?" . http_build_query([
                'chat_id' => $data['chat_id'],
                'text' => $data['text'],
                'parse_mode' => $data['parse_mode'] ?? 'Markdown'
            ]));
        }
    }

    protected static function sendEmail($notification): void
    {
        if (method_exists($notification, 'toMail')) {
            $mail = $notification->toMail();
            $to = Config::get('laralogger.notifications.email_to', []);
            foreach ($to as $recipient) {
                Mail::to($recipient)->send($mail);
            }
        }
    }
}