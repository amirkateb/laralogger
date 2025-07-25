# Laralogger

**Laralogger** is a powerful, queue-ready Laravel error logging package that automatically captures HTTP 4xx/5xx errors and system-level issues, logs them to the database, sends customizable notifications (Telegram, Email), and even analyzes them using AI (GPT).

📄 [مطالعه داکیومنت فارسی](README.fa.md)

---

## 🚀 Features

- ✅ Logs HTTP 4xx/5xx errors with full context
- ✅ Stores logs in database (not files)
- ✅ Smart notifications (Telegram, Email, or custom)
- ✅ Optional AI error analysis via OpenAI (GPT-4/3.5)
- ✅ Queue-supported (notifications & AI)
- ✅ System log scanning (e.g. NGINX error log, 502s)
- ✅ Artisan commands: simulate, cleanup, export, scan
- ✅ Fully configurable via `config/laralogger.php`
- ❌ No UI — focused on automation and performance

---

## 📦 Installation

```bash
composer require amirkateb/laralogger
php artisan vendor:publish --tag=laralogger-config
php artisan migrate
```

---

## ⚙️ Configuration

Edit your `config/laralogger.php` to set:

- `active` → enable/disable
- `environments` → allowed environments (e.g. production, staging)
- `log_status_codes`, `notify_status_codes` → define what gets logged and notified
- `notifications` → enable queue, channels, recipient email(s)
- `ai` → OpenAI API key, model, and prompt
- `system_logs.nginx` → file path, match pattern, auto-store

You can also define a custom notification class via:

```php
'notifier' => \App\Notifications\MyCustomNotifier::class
```

---

## 🔔 Notifications

Supports:

- Telegram (via `bot_token` and `chat_id` from `config/services.php`)
- Email (Laravel Mail)
- Any custom notifier

All notifications can run in queue via:

```php
'queue' => [
  'use_queue' => true,
  'name' => 'notifications',
],
```

---

## 🤖 AI Error Analysis

Optional support for AI-based debugging:

- Requires OpenAI API key
- Supports `gpt-3.5-turbo`, `gpt-4`, or any model
- Saves output in `ai_analysis` field of log

---

## 🧪 Artisan Commands

```bash
php artisan laralog:test --code=500         # Simulate an error
php artisan laralog:cleanup --days=30       # Cleanup old logs
php artisan laralog:scan-nginx-log          # Scan NGINX log for critical issues
```

---

## 🧾 Log Schema

Each log entry contains:

- Status code
- Exception class & message
- Request method, URL, IP, headers, payload (optional)
- User ID, name, and guard (if logged in)
- AI analysis result (if enabled)

---

## 💡 System Log Monitoring

Define paths and match patterns in `config/laralogger.php`, e.g.:

```php
'system_logs' => [
  'nginx' => [
    'enabled' => true,
    'path' => '/var/log/nginx/error.log',
    'pattern' => '/502 Bad Gateway/',
    'send_notification' => true,
    'store_in_db' => true,
    'ai_analysis' => true,
  ]
]
```

Schedule it in your app’s `App\Console\Kernel`:

```php
$schedule->command('laralog:scan-nginx-log')->everyMinute();
```

---

## 📄 License

MIT © 2025 [AmirMohammad KatebSaber](mailto:amveks43@gmail.com)