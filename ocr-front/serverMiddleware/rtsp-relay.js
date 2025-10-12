const express = require('express');
const app = express();
const { proxy } = require('rtsp-relay')(app);

// افزایش MaxListeners برای جلوگیری از warning
require('events').EventEmitter.defaultMaxListeners = 20;

// تنظیمات دوربین‌ها
const cameras = {
  "west_1": {
    name: 'plate',
    url: 'rtsp://admin:Admin@123@192.168.10.10:554/cam/realmonitor?channel=1&subtype=1'
  },
  "east_1": {
    name: 'plate',
    url: 'rtsp://admin:Admin@123@172.23.11.21:554/cam/realmonitor?channel=1&subtype=1'
  },
  "east_2": {
    name: 'plate',
    url: 'rtsp://admin:Admin@123@172.23.12.21:554/cam/realmonitor?channel=1&subtype=1'
  },
  "east_3": {
    name: 'plate',
    url: 'rtsp://admin:Admin@123@172.23.13.21:554/cam/realmonitor?channel=1&subtype=1'
  },
};

// ایجاد handler برای هر دوربین
const handlers = Object.entries(cameras).map(([key, camera]) => ({
  key,
  ...camera,
  handler: proxy({
    url: camera.url,
    verbose: false,
    transport: 'tcp',
  })
}));


// مدیریت active connections
const activeConnections = new Map();

// ثبت routeهای WebSocket برای هر دوربین
handlers.forEach(({ key, handler }) => {
  app.ws(`/cam/${key}`, (ws, req) => {
    const connectionId = Date.now() + Math.random();
    console.log(`New connection for ${key}, ID: ${connectionId}`);

    activeConnections.set(connectionId, ws);

    // مدیریت خطاها
    const errorHandler = (error) => {
      console.error(`WebSocket error for ${key} (${connectionId}):`, error);
      cleanupConnection(connectionId);
    };

    // مدیریت بسته شدن connection
    const closeHandler = () => {
      console.log(`Connection closed for ${key}, ID: ${connectionId}`);
      cleanupConnection(connectionId);
    };

    // اضافه کردن listeners
    ws.on('error', errorHandler);
    ws.on('close', closeHandler);

    // اجرای proxy
    handler(ws, req).catch(errorHandler);
  });
});

// تابع برای پاک کردن connection
function cleanupConnection(connectionId) {
  const ws = activeConnections.get(connectionId);
  if (ws) {
    // حذف همه listeners
    ws.removeAllListeners('error');
    ws.removeAllListeners('close');
    ws.removeAllListeners('message');

    // حذف از لیست connections فعال
    activeConnections.delete(connectionId);

    console.log(`Cleaned up connection ${connectionId}. Active connections: ${activeConnections.size}`);
  }
}

// تابع برای بستن همه connections
function cleanupAllConnections() {
  console.log('Cleaning up all connections...');
  activeConnections.forEach((ws, connectionId) => {
    ws.removeAllListeners();
    try {
      ws.terminate();
    } catch (e) {
      // ignore errors during termination
    }
  });
  activeConnections.clear();
}

// مانیتورینگ حافظه و connections
setInterval(() => {
  const used = process.memoryUsage();
  console.log('Memory & Connections -', {
    connections: activeConnections.size,
    rss: `${Math.round(used.rss / 1024 / 1024)} MB`,
    heapUsed: `${Math.round(used.heapUsed / 1024 / 1024)} MB`
  });
}, 30000); // هر 30 ثانیه

// تمیز کردن هنگام بسته شدن برنامه
process.on('SIGINT', () => {
  console.log('Received SIGINT. Shutting down...');
  cleanupAllConnections();
  process.exit(0);
});

process.on('SIGTERM', () => {
  console.log('Received SIGTERM. Shutting down...');
  cleanupAllConnections();
  process.exit(0);
});

// مدیریت uncaught exceptions
process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
  cleanupAllConnections();
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
  cleanupAllConnections();
  process.exit(1);
});

app.listen(8081, () => {
  console.log(`سرور WebSocket روی پورت ${8081} اجرا شد`);
  console.log('دوربین‌های فعال:');
  handlers.forEach(({ key }) => {
    console.log(`- ${key}: ws://localhost:8081/api/stream/${key}`);
  });
});
