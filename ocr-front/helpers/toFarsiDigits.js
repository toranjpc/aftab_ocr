export default function (number) {
  const id = { '0': '۰', '1': '۱', '2': '۲', '3': '۳', '4': '۴', '5': '۵', '6': '۶', '7': '۷', '8': '۸', '9': '۹' };
  return number.replace(/[^۱-۹.]/g, function (w) {
    return id[w] || w;
  });
}
