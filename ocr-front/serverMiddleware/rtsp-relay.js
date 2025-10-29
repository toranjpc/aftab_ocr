const express = require('express');
const app = express();
const { proxy } = require('rtsp-relay')(app);

require('events').EventEmitter.defaultMaxListeners = 20;

// تنظیمات دوربین‌ها
const cameras = [
  // { name: 'غربی', type: 'plate', group: '1', url: 'rtsp://admin:Admin@123@192.168.10.10:554/cam/realmonitor?channel=1&subtype=1' },
  // { name: 'غربی - کانتینر خوان', type: 'container', group: '1', url: 'rtsp://admin:Admin@123@192.168.10.12:554/cam/realmonitor?channel=1&subtype=1' },
  // { name: 'غربی - کانتینر از بغل', type: 'face', group: '1', url: 'rtsp://admin:Admin@123@192.168.10.15:554/cam/realmonitor?channel=1&subtype=1' },
  // { name: 'شرقی 1', type: 'plate', group: '2', url: 'rtsp://admin:Admin@123@172.23.11.21:554/cam/realmonitor?channel=1&subtype=1' },
  // { name: 'شرقی 1 - کانتینر خوان', type: 'container', group: '2', url: 'rtsp://admin:Admin@123@172.23.11.16:554/cam/realmonitor?channel=1&subtype=1' },
  { name: 'شرقی 2', type: 'plate', group: '3', url: 'rtsp://admin:Admin@123@172.23.12.21:554/cam/realmonitor?channel=1&subtype=1' },
  { name: 'شرقی 2 - کانتینر خوان', type: 'container', group: '3', url: 'rtsp://admin:Admin@123@172.23.12.15:554/cam/realmonitor?channel=1&subtype=1' },
  { name: 'شرقی 3', type: 'plate', group: '4', url: 'rtsp://admin:Admin@123@172.23.13.21:554/cam/realmonitor?channel=1&subtype=1' },
  { name: 'شرقی 3 - کانتینر خوان', type: 'container', group: '4', url: 'rtsp://admin:Admin@123@172.23.13.15:554/cam/realmonitor?channel=1&subtype=1' },
];

// ایجاد handler برای هر دوربین
const handlers = cameras.map((camera) => ({
  routeKey: `${camera.group}/${camera.type}`, // 👈 ترکیب group و type
  ...camera,
  handler: proxy({
    url: camera.url,
    verbose: false,
    transport: 'tcp',
  }),
}));

const activeConnections = new Map();

handlers.forEach(({ routeKey, handler, group, type }) => {
  // مسیر وب‌سوکت بر اساس group و type
  app.ws(`/cam/${group}/${type}`, (ws, req) => {
    const connectionId = Date.now() + Math.random();
    console.log(`New connection for ${routeKey}, ID: ${connectionId}`);

    activeConnections.set(connectionId, ws);

    const errorHandler = (error) => {
      console.error(`WebSocket error for ${routeKey} (${connectionId}):`, error);
      cleanupConnection(connectionId);
    };

    const closeHandler = () => {
      console.log(`Connection closed for ${routeKey}, ID: ${connectionId}`);
      cleanupConnection(connectionId);
    };

    ws.on('error', errorHandler);
    ws.on('close', closeHandler);

    handler(ws, req).catch(errorHandler);
  });
});

// پاکسازی کانکشن‌ها
function cleanupConnection(connectionId) {
  const ws = activeConnections.get(connectionId);
  if (ws) {
    ws.removeAllListeners();
    activeConnections.delete(connectionId);
    console.log(`Cleaned up connection ${connectionId}. Active connections: ${activeConnections.size}`);
  }
}

function cleanupAllConnections() {
  console.log('Cleaning up all connections...');
  activeConnections.forEach((ws) => {
    ws.removeAllListeners();
    try { ws.terminate(); } catch { }
  });
  activeConnections.clear();
}

setInterval(() => {
  const used = process.memoryUsage();
  console.log('Memory & Connections -', {
    connections: activeConnections.size,
    rss: `${Math.round(used.rss / 1024 / 1024)} MB`,
    heapUsed: `${Math.round(used.heapUsed / 1024 / 1024)} MB`
  });
}, 30000);

process.on('SIGINT', () => { cleanupAllConnections(); process.exit(0); });
process.on('SIGTERM', () => { cleanupAllConnections(); process.exit(0); });
process.on('uncaughtException', (err) => { console.error(err); cleanupAllConnections(); process.exit(1); });
process.on('unhandledRejection', (reason, promise) => { console.error(reason); cleanupAllConnections(); process.exit(1); });

app.listen(8081, () => {
  console.log(`✅ سرور WebSocket روی پورت 8081 اجرا شد`);
  console.log('🎥 مسیرهای فعال دوربین‌ها:');
  handlers.forEach(({ routeKey }) => {
    console.log(`- ws://localhost:8081/cam/${routeKey}`);
  });
});
