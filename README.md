# Laralogger
![GitHub Release](https://img.shields.io/github/v/release/amirkateb/laralogger)
![GitHub License](https://img.shields.io/github/license/amirkateb/laralogger)

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
### 🗃️ Log Storage Structure

Laralogger stores all error logs in the database using the `error_logs` table. Each record includes detailed information about the exception, request, user, environment, and optional AI analysis.

#### 📄 Schema Overview

By default, Laralogger creates the following columns in the `error_logs` table:

| Column              | Description                                      |
|---------------------|--------------------------------------------------|
| `id`               | Primary key                                      |
| `message`          | Exception message                                |
| `status_code`      | HTTP status code (e.g., 404, 500)                |
| `exception_class`  | Class name of the exception                      |
| `file`             | File path where exception occurred               |
| `line`             | Line number                                      |
| `url`              | Request URL                                      |
| `method`           | HTTP method (GET, POST...)                       |
| `user_id`          | ID of authenticated user (nullable)             |
| `user_type`        | Guarded class (e.g., App\Models\User)            |
| `headers`          | Full request headers (JSON)                      |
| `payload`          | Request body (JSON)                              |
| `ip`               | Request IP address                               |
| `user_agent`       | User’s browser/device info                       |
| `ai_analysis`      | Optional AI-generated explanation                |
| `created_at`       | Timestamp of the error                           |

#### 📍 Migration

To publish and run the migration:

```bash
php artisan vendor:publish --tag=laralogger-migrations
php artisan migrate
```

You can customize the migration to add extra columns if needed.
---

## 🧾 Log Schema

Each log entry contains:

- Status code
- Exception class & message
- Request method, URL, IP, headers, payload (optional)
- User ID, name, and guard (if logged in)
- AI analysis result (if enabled)

---

### 📛 Real-time Nginx Log Scanner

Laralogger can optionally monitor your Nginx error logs in real-time to catch server-level issues such as 502 Bad Gateway or 504 Gateway Timeout, even before they reach Laravel.

#### 🔧 Configuration

In `config/laralogger.php`:

```php
'nginx_monitoring' => [
    'enabled' => true,
    'log_path' => '/var/log/nginx/error.log',
    'patterns' => [
        '502 Bad Gateway',
        '504 Gateway Timeout',
    ],
    'interval' => 10, // in seconds
],
```

#### ⚙️ How It Works

- A background process (you can schedule it via cron or run as a systemd service) reads the last few lines of the Nginx error log.
- If any pattern matches (e.g., 502), it creates a new error log and sends notifications immediately.

#### 🧪 Example Command

```bash
php artisan laralog:watch-nginx
```

You may use this inside a scheduled task or create a background service like:

```bash
* * * * * php /path/to/artisan laralog:watch-nginx >> /dev/null 2>&1
```

You can extend this to monitor multiple log files as well.
---

### 🔍 Webhook Logging and Route Monitoring

Laralogger also supports **optional logging** of non-error HTTP requests such as:

- 2xx (Successful) responses
- 3xx (Redirects)
- Specific routes (e.g., payment gateways, webhooks)

This allows you to analyze traffic patterns, API behavior, or debug critical routes.

#### 🔧 Configuration

```php
'request_monitoring' => [
    'enabled' => true,
    'log_success' => true,
    'log_redirects' => true,
    'only_routes' => [ // Leave empty to log all
        'payment.callback',
        'webhook.telegram',
    ],
    'exclude_methods' => ['OPTIONS'],
],
```

#### 🗂️ Where Are They Logged?

These logs are stored in the same `error_logs` table, with a `status_code` like `200`, `302`, etc. They are marked with a `non_exception` flag internally to differentiate.

#### 📊 Use Cases

- **Payment debugging**: Know what data was sent/received to/from gateways.
- **Webhook tracing**: Know exactly when and what payload came in.
- **Traffic insights**: Spot high-traffic or redirect-heavy endpoints.

---

## 📄 License

MIT © 2025 [AmirMohammad KatebSaber](mailto:amveks43@gmail.com)