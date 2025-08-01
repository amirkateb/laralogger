# لارالاگر (Laralogger)
![GitHub Release](https://img.shields.io/github/v/release/amirkateb/laralogger)
![GitHub License](https://img.shields.io/github/license/amirkateb/laralogger)
**Laralogger** یک پکیج قدرتمند برای لاگ‌گیری خطا در لاراول است که به‌صورت خودکار خطاهای HTTP سری ۴۰۰ و ۵۰۰ را ثبت می‌کند، نوتیفیکیشن‌های هوشمند به تلگرام و ایمیل می‌فرستد، خطاهای سیستمی مانند Nginx را بررسی می‌کند، و در صورت نیاز، آن‌ها را با استفاده از GPT تحلیل می‌کند.

📄 [Read this in English](README.md)

---

## 🚀 امکانات

- ✅ ثبت دقیق خطاهای ۴xx و ۵xx به‌صورت خودکار
- ✅ ذخیره لاگ‌ها در دیتابیس (نه فایل)
- ✅ نوتیفیکیشن تلگرام، ایمیل یا سفارشی
- ✅ تحلیل هوش مصنوعی (GPT-4/3.5)
- ✅ اجرای همه موارد از طریق صف (Queue)
- ✅ بررسی لاگ‌های سیستمی مثل `nginx error.log`
- ✅ دستورات artisan برای تست، پاکسازی، اسکن و خروجی
- ❌ بدون رابط گرافیکی برای امنیت و کارایی بیشتر

---

## 📦 نصب

```bash
composer require amirkateb/laralogger
php artisan vendor:publish --tag=laralogger-config
php artisan migrate
```

---

## ⚙️ پیکربندی

فایل `config/laralogger.php` شامل موارد زیر است:

- فعال/غیرفعال بودن
- محیط‌های مجاز برای اجرا (`production`, `staging`, ...)
- کدهای HTTP که باید ذخیره یا نوتیف شوند
- تنظیمات نوتیفیکیشن و صف
- کلید و پرامپت GPT برای تحلیل هوشمند
- مسیر فایل لاگ nginx و الگوی تشخیص (مثلاً خطای 502)

---

### 📣 سیستم نوتیفیکیشن

لارالاگر قابلیت ارسال نوتیفیکیشن خطا از طریق کانال‌های مختلف مانند تلگرام، ایمیل یا کلاس‌های سفارشی را دارد. نوتیفیکیشن‌ها بلافاصله پس از ثبت هر خطا فعال می‌شوند.

#### 🔧 تنظیمات

در فایل `config/laralogger.php` بخش زیر را پیکربندی کنید:

```php
'notification' => [
    'enabled' => true,
    'channels' => ['telegram', 'email'], // یا ['custom']
    'queue' => true,
    'queue_name' => 'notifications',

    // اختیاری: استفاده از کلاس نوتیفیکیشن سفارشی
    'custom_notifier' => \App\Notifications\CustomErrorNotifier::class,
],
```

#### 🧩 نوتیفایرهای داخلی

- `Laralogger\Notifications\TelegramNotifier`
- `Laralogger\Notifications\EmailNotifier`

می‌توانید کلاس دلخواه خودتان را بسازید که از اینترفیس `Laralogger\Contracts\NotifiableInterface` پیروی کند و آن را در تنظیمات وارد کنید.

#### 🧪 مثال استفاده

```php
use Laralogger\Models\ErrorLog;
use Laralogger\Services\NotificationManager;

$log = ErrorLog::latest()->first();
NotificationManager::notify($log);
```

در صورت فعال بودن، همه نوتیفیکیشن‌ها می‌توانند از طریق صف ارسال شوند.

---

 ### 🤖 تحلیل خطا با کمک هوش مصنوعی (AI)

لارالاگر به‌صورت اختیاری امکان تحلیل خطا با استفاده از مدل‌های هوش مصنوعی (مثل GPT-4 یا GPT-3.5) را فراهم می‌کند. در صورت فعال‌سازی، خلاصه‌ای از اطلاعات خطا برای مدل انتخاب‌شده ارسال می‌شود و پاسخ تحلیلی (شامل علت احتمالی و راه‌حل پیشنهادی) در دیتابیس ذخیره می‌گردد.

#### 🔧 فعال‌سازی تحلیل با هوش مصنوعی

فایل `config/laralogger.php` را ویرایش کرده و بخش `ai` را به‌صورت زیر تنظیم کنید:

```php
'ai' => [
    'enabled' => true,
    'provider' => 'openai',
    'api_key' => env('LARALOGGER_AI_API_KEY'),
    'model' => 'gpt-4', // یا 'gpt-3.5-turbo'
    'prompt' => "You are an expert Laravel backend developer. Given this error, explain the root cause and suggest a fix:\n\n{{error}}",
    'queue' => true, // اجرا از طریق صف
    'queue_name' => 'ai-analysis',
],
```

سپس مقدار زیر را در `.env` تنظیم کنید:

```
LARALOGGER_AI_API_KEY=sk-xxxxxx
```

> ☝️ در رشته‌ی `prompt` می‌توانید از `{{error}}` برای قرار دادن محتوای خطا به‌صورت خودکار استفاده کنید.

#### 📦 خروجی

- پاسخ تولیدشده توسط هوش مصنوعی در فیلد `ai_analysis` جدول `error_logs` ذخیره می‌شود.
- اگر صف فعال باشد، این فرآیند به‌صورت غیرهم‌زمان (async) انجام خواهد شد.
- می‌توانید متن prompt را برای لحن یا کاربرد خاص خودتان سفارشی‌سازی کنید.

#### 🧪 مثال تست

برای تست:

```bash
php artisan laralog:test --code=500
```

سپس رکورد لاگ ایجاد‌شده در دیتابیس را بررسی کنید — پاسخ تحلیلی هوش مصنوعی باید به آن اضافه شده باشد.

---

## 🧪 دستورات Artisan

```bash
php artisan laralog:test --code=500         # شبیه‌سازی خطا
php artisan laralog:cleanup --days=30       # حذف لاگ‌های قدیمی
php artisan laralog:scan-nginx-log          # بررسی لاگ‌های nginx
```

---

### 🗃️ ساختار ذخیره‌سازی لاگ‌ها

لارالاگر تمام خطاها را در جدول `error_logs` دیتابیس ذخیره می‌کند. هر رکورد شامل جزئیات کاملی از خطا، درخواست، کاربر، محیط اجرا و در صورت فعال بودن، تحلیل AI می‌باشد.

#### 📄 نمای کلی اسکیمای جدول

به‌صورت پیش‌فرض، ستون‌های زیر در جدول `error_logs` ایجاد می‌شوند:

| ستون               | توضیح                                              |
|--------------------|----------------------------------------------------|
| `id`               | شناسه اصلی رکورد                                   |
| `message`          | پیام خطای دریافت‌شده                              |
| `status_code`      | کد وضعیت HTTP (مثلاً 500 یا 404)                  |
| `exception_class`  | نام کلاس خطا (Exception)                          |
| `file`             | فایل رخداد خطا                                    |
| `line`             | شماره خط                                           |
| `url`              | آدرس درخواست‌شده                                  |
| `method`           | متد HTTP (مثلاً GET, POST)                        |
| `user_id`          | شناسه کاربر (در صورت ورود)                        |
| `user_type`        | نوع مدل کاربر (مثلاً App\Models\User)            |
| `headers`          | هدرهای کامل درخواست (فرمت JSON)                  |
| `payload`          | محتوای بدنه درخواست (فرمت JSON)                  |
| `ip`               | آدرس IP کاربر                                     |
| `user_agent`       | اطلاعات مرورگر یا دستگاه کاربر                    |
| `ai_analysis`      | تحلیل هوش مصنوعی (در صورت فعال بودن)             |
| `created_at`       | زمان ثبت خطا                                       |

#### 📍 ساخت جدول

برای ساخت جدول، دستور زیر را اجرا کنید:

```bash
php artisan vendor:publish --tag=laralogger-migrations
php artisan migrate
```

در صورت نیاز می‌توانید مایگریشن را شخصی‌سازی کرده و ستون‌های بیشتری اضافه کنید.

---

## 🧾 ساختار لاگ

هر رکورد لاگ شامل موارد زیر است:

- کد وضعیت (۴xx، ۵xx)
- پیام و کلاس Exception
- URL، method، IP، header و payload (در صورت فعال بودن)
- اطلاعات کاربر لاگین‌شده (ID، نام، گارد)
- نتیجه تحلیل AI (در صورت فعال بودن)

---

### 📛 اسکن زنده لاگ‌های Nginx

لارالاگر می‌تواند به‌صورت اختیاری، فایل لاگ خطاهای Nginx را مانیتور کند تا مشکلات سطح سرور مانند 502 Bad Gateway یا 504 Timeout را سریعاً شناسایی و ثبت کند، حتی قبل از رسیدن درخواست به Laravel.

#### 🔧 تنظیمات

در فایل `config/laralogger.php` بخش زیر را پیکربندی کنید:

```php
'nginx_monitoring' => [
    'enabled' => true,
    'log_path' => '/var/log/nginx/error.log',
    'patterns' => [
        '502 Bad Gateway',
        '504 Gateway Timeout',
    ],
    'interval' => 10, // هر چند ثانیه یک‌بار بررسی شود
],
```

#### ⚙️ نحوه عملکرد

- یک پردازش پس‌زمینه (مثلاً از طریق cronjob یا systemd) فایل لاگ Nginx را بررسی می‌کند.
- اگر یکی از الگوهای خطا (مثلاً 502) در لاگ دیده شود، یک خطای جدید ثبت و فوراً نوتیفیکیشن ارسال می‌شود.

#### 🧪 اجرای دستی

```bash
php artisan laralog:watch-nginx
```

برای اجرای مداوم می‌توانید از `cron` استفاده کنید:

```bash
* * * * * php /path/to/artisan laralog:watch-nginx >> /dev/null 2>&1
```

همچنین می‌توانید مسیرهای متعددی را برای مانیتورینگ تنظیم کنید.

---

### 🔍 ثبت درخواست‌های موفق و ریدایرکت

لارالاگر قابلیت ثبت درخواست‌های غیرخطا را نیز دارد، مثل:

- پاسخ‌های موفق (۲xx)
- ریدایرکت‌ها (۳xx)
- مسیرهای خاص مثل callbackهای پرداخت یا webhookها

این امکان به شما کمک می‌کند مسیرهای حیاتی را ردیابی و تحلیل کنید.

#### 🔧 تنظیمات

```php
'request_monitoring' => [
    'enabled' => true,
    'log_success' => true,
    'log_redirects' => true,
    'only_routes' => [ // اگر خالی باشد، همه لاگ می‌شوند
        'payment.callback',
        'webhook.telegram',
    ],
    'exclude_methods' => ['OPTIONS'],
],
```

#### 🗂️ محل ذخیره لاگ‌ها

این لاگ‌ها نیز در جدول `error_logs` ذخیره می‌شوند، اما دارای کد وضعیت `200` یا `302` و بدون exception هستند. سیستم به‌صورت داخلی آن‌ها را از خطاها متمایز می‌کند.

#### 📊 موارد استفاده

- **دیباگ پرداخت**: بررسی جزئیات رفت‌وآمد به درگاه‌های بانکی
- **ردیابی وبهوک‌ها**: ثبت دقیق زمان و محتوای ورودی
- **تحلیل ترافیک**: شناسایی مسیرهای پرتردد یا دارای ریدایرکت زیاد

---

## 📄 مجوز

MIT © 2025 [امیرمحمد کاتب صابر](mailto:amveks43@gmail.com)