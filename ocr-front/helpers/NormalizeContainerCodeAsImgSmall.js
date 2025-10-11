export default function (v) {
  try {
    if (v) {
      const regex = /^([A-Za-z]+)(\d{6})(\d)(.*)$/
      const split = v.match(regex)
      if (split) {
        const part1 = split[1] // حروف ابتدایی
        const part2 = split[2] // 6 رقم بعدی
        const part3 = split[3] // تک رقم
        const part4 = split[4] // بقیه رشته

        console.log('Part 1:', part1)
        console.log('Part 2:', part2)
        console.log('Part 3:', part3)
        console.log('Part 4:', part4)
      } else {
        return v
      }
      return (
        '<div style="font-size: 12px;border-radius: 4px;background-size: contain;display: flex;flex-direction: row-reverse;width: fit-content;font-family: sans-serif !important;padding-left: 4px;color: #ffcc29;background: #2957a4;">' +
        '<span style="line-height: 19px;">' +
        split[1] +
        '</span>' +
        '<div style="margin: 2px 3px 2px 4px;display: flex;flex-direction: column;width: fit-content;text-align: left;line-height: 15px;">' +
        split[2] +
        '<span>' +
        split[4] +
        '</span></div>' +
        '<span style="border: 1px solid;padding: 0px 3px;height: fit-content;margin-right: 6px;line-height: 13px;margin-top: 3px;padding-top: 1px;">' +
        split[3] +
        '</span>' +
        '</div>'
      )
    }
    return 'بدون مقدار'
  } catch (e) {}
}
