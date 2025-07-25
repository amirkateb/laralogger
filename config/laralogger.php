<?php

return [

    'active' => env('LARALOGGER_ENABLED', true),

    'environments' => ['production', 'staging'],

    'log_status_codes' => [400, 401, 403, 404, 422, 429, 500, 503],
    'notify_status_codes' => [500, 422, 403],

    'include_payload' => true,
    'include_headers' => true,
    'include_trace' => false,

    'guard_detection' => true,

    'storage' => 'database',

    'notifications' => [
        'enabled' => true,
        'channels' => ['telegram', 'email'],
        'queue' => [
            'use_queue' => true,
            'name' => 'notifications',
        ],
        'email_to' => ['admin@example.com'],
        'notifier' => null,
    ],

    'ai' => [
        'enabled' => true,
        'driver' => 'openai',
        'model' => env('LARALOGGER_AI_MODEL', 'gpt-4'),
        'secret' => env('LARALOGGER_AI_SECRET'),
        'prompt' => env('LARALOGGER_AI_PROMPT', 'خطای زیر را بررسی کن و دلیل و راه‌حل احتمالی آن را بگو:'),
    ],

    'system_logs' => [
        'nginx' => [
            'enabled' => true,
            'path' => '/var/log/nginx/error.log',
            'pattern' => '/502 Bad Gateway/',
            'send_notification' => true,
            'store_in_db' => true,
            'ai_analysis' => true,
        ],
    ],

    'request_monitoring' => [
        'enabled' => false,
        'only_methods' => ['POST', 'PUT'],
        'only_statuses' => [200, 302],
    ],
];