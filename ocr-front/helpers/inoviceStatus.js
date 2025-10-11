export default function () {
  const inoviceStatus = {
    title: 'وضعیت',
    field: 'status',
    type: 'select',
    rel: false,
    isHeader: true,
    col: { md: 6 },
    showIn: ['show'],
    default: 'enter',
    values: [
      { text: 'وارد شده', value: 'enter' },
      { text: 'مراجع به سکو', value: 'in_station' },
      { text: 'شروع بازرسی', value: 'lock_in_station' },
      { text: 'در انتظار تسویه نهایی', value: 'get_factor' },
      { text: 'تسویه شده (اجازه خروج)', value: 'have_leave_station' },
      { text: 'خارج شده از سکو', value: 'leave_station' },
      { text: 'خارج شده', value: 'exit' },
    ],
    inList(status, form, filde) {
      const values = filde.values
      switch (status) {
        case 'enter':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption success  white--text">${values[0].text}</span></div>`
        case 'in_station':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  orange white--text">${values[1].text}</span></div>`
        case 'lock_in_station':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption error  white--text">${values[2].text}</span></div>`
        case 'get_factor':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  blue white--text">${values[3].text}</span></div>`
        case 'have_leave_station':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  teal white--text">${values[4].text}</span></div>`
        case 'leave_station':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  purple white--text">${values[5].text}</span></div>`
        case 'exit':
          return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  gray white--text">${values[6].text}</span></div>`
        default:
          break
      }
    },
  }
  return inoviceStatus
}
