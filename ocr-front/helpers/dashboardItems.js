export default [
  {
    link: true,
    to: '/admin/dashboard',
    text: 'داشبورد',
    icon: 'tachometer-alt-slowest',
  },

  {
    text: 'مانیتورینگ (غربی)',
    icon: 'fal fa-cctv',
    link: false,
    isGroup: true,
    children: [
      {
        link: true,
        to: '/admin/ocr',
        text: 'دوربین ocr',
        icon: 'cctv',
        staticData: '1'
      },

      {
        link: true,
        to: '/admin/ocr/match/west_1',
        text: 'مچینگ',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/gate/west',
        text: 'ورود/خروج',
        icon: 'truck',
      },

      {
        link: true,
        to: '/admin/ocr/report',
        text: 'گزارش ترددها',
        icon: 'chart-area',
      },
      {
        link: true,
        to: '/admin/camera/1',
        text: 'دوربین‌ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/log-receiver',
        text: 'سرویس ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/monitoring/1',
        text: 'گیت (نگهبانی)',
        icon: 'box',
      },

      {
        to: '/admin/gcoms/gcoms-out/1',
        text: 'کوتاژ',
        icon: 'truck-fast',
        link: true,
      },
    ],
  },

  {
    text: 'مانیتورینگ (شرقی 1)',
    icon: 'fal fa-cctv',
    link: false,
    isGroup: true,
    children: [
      {
        link: true,
        to: '/admin/ocr',
        text: 'دوربین ocr',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/match/east_1',
        text: 'مچینگ',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/gate/west',
        text: 'ورود/خروج',
        icon: 'truck',
      },

      {
        link: true,
        to: '/admin/ocr/report',
        text: 'گزارش ترددها',
        icon: 'chart-area',
      },
      {
        link: true,
        to: '/admin/camera/2',
        text: 'دوربین‌ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/log-receiver',
        text: 'سرویس ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/monitoring/2',
        text: 'گیت (نگهبانی)',
        icon: 'box',
      },

      {
        to: '/admin/gcoms/gcoms-out/2',
        text: 'کوتاژ',
        icon: 'truck-fast',
        link: true,
      },
    ],
  },

  {
    text: 'مانیتورینگ (شرقی 2)',
    icon: 'fal fa-cctv',
    link: false,
    isGroup: true,
    children: [
      {
        link: true,
        to: '/admin/ocr',
        text: 'دوربین ocr',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/match/east_2',
        text: 'مچینگ',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/gate/west',
        text: 'ورود/خروج',
        icon: 'truck',
      },

      {
        link: true,
        to: '/admin/ocr/report',
        text: 'گزارش ترددها',
        icon: 'chart-area',
      },
      {
        link: true,
        to: '/admin/camera/3',
        text: 'دوربین‌ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/log-receiver',
        text: 'سرویس ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/monitoring/3',
        text: 'گیت (نگهبانی)',
        icon: 'box',
      },

      {
        to: '/admin/gcoms/gcoms-out/3',
        text: 'کوتاژ',
        icon: 'truck-fast',
        link: true,
      },
    ],
  },

  {
    text: 'مانیتورینگ (شرقی 3)',
    icon: 'fal fa-cctv',
    link: false,
    isGroup: true,
    children: [
      {
        link: true,
        to: '/admin/ocr',
        text: 'دوربین غربی ocr',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/match/east_3',
        text: 'مچینگ',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/ocr/gate/west',
        text: 'ورود/خروج',
        icon: 'truck',
      },

      {
        link: true,
        to: '/admin/ocr/report',
        text: 'گزارش ترددها',
        icon: 'chart-area',
      },
      {
        link: true,
        to: '/admin/camera/4',
        text: 'دوربین‌ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/log-receiver',
        text: 'سرویس ها',
        icon: 'cctv',
      },

      {
        link: true,
        to: '/admin/monitoring/4',
        text: 'گیت غربی (نگهبانی)',
        icon: 'box',
      },

      {
        to: '/admin/gcoms/gcoms-out/4',
        text: 'کوتاژ',
        icon: 'truck-fast',
        link: true,
      },
    ],
  },

  {
    text: 'کاربران',
    icon: 'fal fa-users-gear',
    link: false,
    isGroup: true,
    children: [
      {
        link: true,
        to: '/admin/users',
        text: 'کاربران',
        icon: 'users',
      },
      {
        link: true,
        to: '/admin/user-level-permission',
        text: 'نقش کاربران',
        icon: 'user-tag',
      },
      {
        link: true,
        to: '/admin/user-pass-change',
        text: 'تغییر رمز',
        icon: 'key',
      },
    ],
  },
]
