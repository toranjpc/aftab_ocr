export default function (v) {
  if (!v) return '-'
  else
    return (
      (v?.d ? v?.d + ' روز و ' : '') +
      (v?.h ? v?.h + ' ساعت و ' : '') +
      (v?.i ? v?.i + ' دقیقه ' : '')
    )
}
