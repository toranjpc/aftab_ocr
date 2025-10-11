import { afghan } from "./NormalizeVehicleNumberAsImg"
import { getCity } from "./NormalizeVehicleNumberAsImg"

export default function (v) {
  const ein = v?.split('ein')[1] != null ? '<span>ع</span>' : ''
  return ein
    ? `<span class="d-flex flex-row-reverse  align-center" style="justify-content: flex-end">
        <span>
          ${v?.split('ein')[0]}
        </span>` +
        ein +
        `<span>
        ${v?.split('ein')[1].substring(3, 5)}
        ایران
        ${v?.split('ein')[1].substring(0, 3)}
        </span>
      </span>`
    : `<span class="d-flex flex-row-reverse align-center"style="justify-content: flex-end">
      <span>
        ${v?.split('ein')[0]}
      </span>`
}

export const toString = (v) => {
  const map = {
    ein: 'ع',
    ta: 'ط',
    n: 'ن',
    sad: 'ص',
    q: 'ق',
    l: 'ل',
    s: 'س',
    y: 'ی',
    h: 'ه',
    d: 'د',
    m: 'م',
    b: 'ب',
  }
  for (const key in map) {
    if (v.includes(key)) {
      v = v.replace(key, map[key])
    }
  }

  v = v.split('')
  v.splice(6, 0, '-')

  return v.join('')
}

export const reverseToString = (v, type = 'iran') => {
  if (v === undefined) {
    return '-'
  }
  if (type === 'iran') {
    const map = {
      ein: 'ع',
      ta: 'ط',
      n: 'ن',
      sad: 'ص',
      q: 'ق',
      l: 'ل',
      s: 'س',
      y: 'ی',
      h: 'ه',
      d: 'د',
      m: 'م',
      b: 'ب',
    }
    for (const key in map) {
      if (v.includes(key)) {
        // v = v.split(key).reverse().join(key)
        v = v.replace(key, map[key])
      }
    }

    v = v.split('')
    v = '' + v[6] + v[7] + 'ایران' + v[3] + v[4] + v[5] + v[2] + v[0] + v[1]

    return v
  }
  else if (type === 'afghan') {
    return  `ل${v.split(',')[1]}${getCity(v.split(',')[0])[1]}`
  }
}
