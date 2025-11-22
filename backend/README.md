## System Requirements

- **Laravel Version:** 8.x  
- **PHP Version:** 8.2  
- **Required Services / Tools:**
  - Apache / Nginx
  - MySQL 8.0
  - Redis
  - `nssm-2.24` (یا هر ابزار مشابه برای اجرای Laravel Worker)
  - Task Scheduler (Windows Task Scheduler / Cron)

---

## Scheduler & Worker

### Run Scheduler
برای اجرای زمان‌بند لاراول:

```bash
php artisan schedule:run

Run Queue Worker
برای اجرای صف‌ها:
php artisan queue:work --daemon --sleep=1 --tries=3


Redis Configuration
مقادیر لازم در فایل .env:
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CLIENT=phpredis
