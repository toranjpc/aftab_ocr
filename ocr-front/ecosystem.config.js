module.exports = {
  apps: [
    {
      name: "rtsp-relay-server",
      script: "rtsp-server.js", // فایل اصلی سرور شما
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: "1G",
      env: {
        NODE_ENV: "production",
        PORT: 2000
      },
      // تنظیمات خاص برای لاگ‌گیری
      log_file: "logs/combined.log",
      error_file: "logs/error.log",
      out_file: "logs/out.log",
      merge_logs: true,
      time: true
    }
  ]
};