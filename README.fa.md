# لارالاگر (Laralogger)

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
composer require your-vendor/laralogger
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

## 🔔 نوتیفیکیشن

پشتیبانی از:

- تلگرام (از طریق `bot_token` و `chat_id`)
- ایمیل (سیستم mail لاراول)
- کلاس سفارشی برای نوتیف دلخواه

امکان اجرا در صف:

```php
'queue' => [
  'use_queue' => true,
  'name' => 'notifications',
],
```

---

## 🤖 تحلیل هوشمند خطا با GPT

در صورت فعال‌سازی:
- درخواست به OpenAI ارسال می‌شود
- تحلیل در فیلد `ai_analysis` ذخیره می‌شود
- قابل استفاده برای گزارش، مانیتورینگ یا دیباگ

---

## 🧪 دستورات Artisan

```bash
php artisan laralog:test --code=500         # شبیه‌سازی خطا
php artisan laralog:cleanup --days=30       # حذف لاگ‌های قدیمی
php artisan laralog:scan-nginx-log          # بررسی لاگ‌های nginx
```

---

## 🧾 ساختار لاگ

هر رکورد لاگ شامل موارد زیر است:

- کد وضعیت (۴xx، ۵xx)
- پیام و کلاس Exception
- URL، method، IP، header و payload (در صورت فعال بودن)
- اطلاعات کاربر لاگین‌شده (ID، نام، گارد)
- نتیجه تحلیل AI (در صورت فعال بودن)

---

## 🧠 پایش لاگ‌های سیستم

مثال پیکربندی برای nginx در `config/laralogger.php`:

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

در `App\Console\Kernel` زمان‌بندی کنید:

```php
$schedule->command('laralog:scan-nginx-log')->everyMinute();
```

---

## 📄 مجوز

MIT © 2025 [امیرمحمد کاتب صابر](mailto:amveks43@gmail.com)