// rtsp-server.js - نسخه اصلاح‌شده کامل با پشتیبانی ffmpeg-static
// --------------------------------------------------------------

const express = require('express');
const app = express();

// پشتیبانی WebSocket‌
const expressWs = require('express-ws')(app);

// درست‌ترین روش برای اتصال rtsp-relay به اپ express
const { proxy } = require('rtsp-relay')(app);

// 🔧 تنظیم ffmpeg-static برای استفاده داخلی (باید قبل از استفاده از proxy باشد)
const ffmpegPath = require('ffmpeg-static');
process.env.FFMPEG_PATH = ffmpegPath;
console.log('✅ ffmpeg path set to:', ffmpegPath);

// 1️⃣ لیست دوربین‌ها (رمز @ باید encode شود → %40)
const cameras = {
  main: 'rtsp://admin:Admin%40123@192.168.10.10:554',
  main2: 'rtsp://admin:Admin%40123@192.168.10.11:554',

  // در صورت نیاز، بعدا می‌تونی دوربین‌های دیگر اضافه کنی:
  // face: 'rtsp://user:password@192.168.1.101:554/Streaming/Channels/101',
  // plate: 'rtsp://user:password@192.168.1.102:554/Streaming/Channels/101',
  // container: 'rtsp://user:password@192.168.1.103:554/Streaming/Channels/101',
};

// 2️⃣ مسیر WS برای هر دوربین
Object.entries(cameras).forEach(([name, url]) => {
  app.ws(`/ws/stream/${name}`, proxy({ url, verbose: true }));
  console.log(`🎥 Stream '${name}' ready at ws://localhost:9001/ws/stream/${name}`);
});

// 🔹 3️⃣ مسیر تست WebSocket
app.ws('/ws/test', (ws, req) => {
  console.log('[✅ Test WS Connected]');
  ws.send('✅ WebSocket test connection successful');
  ws.on('message', msg => ws.send(`Echo from server: ${msg}`));
  ws.on('close', () => console.log('[❌ Test WS Closed]'));
});

const path = require('path');
app.use('/', express.static(path.join(__dirname)));

// 4️⃣ راه‌اندازی سرور
const PORT = 9001;
app.listen(PORT, () => {
  console.log(`🚀 RTSP Relay Server running on ws://localhost:${PORT}`);
});
