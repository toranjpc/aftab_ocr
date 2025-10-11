const express = require('express');
const app = express();
const { proxy } = require('rtsp-relay')(app);
// const mysql = require('mysql2/promise');

// تنظیمات دوربین‌ها
const cameras = [
  // {
  //   name: 'plate',
  //   // url: 'rtsp://admin:Admin@123@192.168.10.13:554'
  //   url: 'rtsp://admin:Admin@123@192.168.10.10:554/cam/realmonitor?channel=1&subtype=1'
  // },
  // {
  //   name: 'container',
  //   url: 'rtsp://admin:Admin@123@192.168.10.11:554'
  // }
];

// ایجاد handler برای هر دوربین
const handlers = cameras.map(camera => ({
  ...camera,
  handler: proxy({
    url: camera.url,
    verbose: false,
    transport: 'tcp',
    // additionalFlags: ['-fflags', 'nobuffer', '-flags', 'low_delay', '-tune', 'zerolatency'],
    // initialBufferLength: 100, // کاهش بافر اولیه
    // maxBufferLength: 200 // کاهش حداکثر بافر
  })
}));

// ثبت routeهای WebSocket برای هر دوربین
handlers.forEach(({ name, handler }) => {
  app.ws(`/api/stream/${name}`, (ws, req) => {
    handler(ws, req).catch(err => {
      console.error(`WebSocket error for ${name}:`, err);
      ws.close();
    });
  });
});

app.listen(2000, () => {
  console.log(`سرور WebSocket روی پورت ${2000} اجرا شد`);
  console.log('دوربین‌های فعال:');
  handlers.forEach(({ name, route }) => {
    console.log(`- ${name}: ws://localhost:2000/api/stream/${name}`);
  });
});
