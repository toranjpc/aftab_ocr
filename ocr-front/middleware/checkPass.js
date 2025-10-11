export default function (context) {
  // لیست مسیرهایی که می‌خواهید بررسی تغییر کلمه عبور روی آنها انجام نشود
  const excludedRoutes = ['admin-user-pass-change'];

  // بررسی اگر مسیر فعلی در لیست مستثنی‌ها است
  if (excludedRoutes.includes(context.route.name)) {
    return;
  }

  const lastPassChange = context.$auth.user.last_pass_change;

  // بررسی اگر تاریخ آخرین تغییر پسورد null است
  if (lastPassChange === null) {
    return context.redirect('/admin/user-pass-change');
  }

  // تبدیل رشته به تاریخ
  const lastChangeDate = new Date(lastPassChange);

  // تاریخ فعلی
  const now = new Date();

  // محاسبه تفاوت زمانی به میلی‌ثانیه
  const timeDiff = now - lastChangeDate;

  // تبدیل تفاوت زمانی به روز
  const daysDiff = timeDiff / (1000 * 60 * 60 * 24);

  // بررسی اینکه اختلاف زمانی بیشتر از یک ماه (30 روز) است یا نه
  if (daysDiff > 30 * 60) {
    return context.redirect('/admin/user-pass-change');
  }
}
