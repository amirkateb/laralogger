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

### 📣 Notification System

Laralogger supports sending error notifications via multiple channels such as Telegram, email, or custom handlers. Notifications are triggered after each error is logged.

#### 🔧 Configuration

In `config/laralogger.php`, define the channels and options:

```php
'notification' => [
    'enabled' => true,
    'channels' => ['telegram', 'email'], // or ['custom']
    'queue' => true,
    'queue_name' => 'notifications',

    // Optional: use your own notification class
    'custom_notifier' => \App\Notifications\CustomErrorNotifier::class,
],
```

#### 🧩 Built-in Notifiers

- `Laralogger\Notifications\TelegramNotifier`
- `Laralogger\Notifications\EmailNotifier`

You can add your own class implementing `Laralogger\Contracts\NotifiableInterface` and plug it into the configuration.

#### 🧪 Example

```php
use Laralogger\Models\ErrorLog;
use Laralogger\Services\NotificationManager;

$log = ErrorLog::latest()->first();
NotificationManager::notify($log);
```

All notifiers support queue-based delivery if enabled.
---

### 🤖 AI-Powered Error Analysis

`Laralogger` provides optional support for AI-driven error diagnostics using OpenAI (e.g. GPT-4 or GPT-3.5). When enabled, it automatically sends a summarized error context to the selected model and stores the response (suggested cause/fix) in your database.

#### 🔧 Enable AI Analysis

In `config/laralogger.php`, update the `ai` section:

```php
'ai' => [
    'enabled' => true,
    'provider' => 'openai',
    'api_key' => env('LARALOGGER_AI_API_KEY'),
    'model' => 'gpt-4', // or 'gpt-3.5-turbo'
    'prompt' => "You are an expert Laravel backend developer. Given this error, explain the root cause and suggest a fix:\n\n{{error}}",
    'queue' => true, // Run analysis via queue
    'queue_name' => 'ai-analysis',
],
```

Then, set the environment variable:

```
LARALOGGER_AI_API_KEY=sk-xxxxxx
```

> ☝️ The `prompt` supports `{{error}}` as a placeholder that will be replaced with error details automatically.

#### 📦 Output

- The AI-generated explanation will be saved to the `ai_analysis` field in the `error_logs` table.
- If queue is enabled, analysis will be processed asynchronously.
- You can customize the `prompt` to fit your use-case or tone.

#### 🧪 Example

Run this to test:

```bash
php artisan laralog:test --code=500
```

Check your database — you should see an AI-generated explanation added to the test log entry.
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