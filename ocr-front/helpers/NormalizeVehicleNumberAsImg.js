// نوع دیفالت برای پلاک
const DEFAULT_TYPE = 'iran';

export default function formatPlate(v, type = DEFAULT_TYPE, edit = false, v2 = null, noplace = 0) {
  try {
    if (!v && !v2) return '';
    if (type === 'iran-regular') type = 'iran';
    // console.log(v + " -> " + v2)
    if (type === 'afghan' || v2?.includes("L") || (v2 && v2.length < 5)) {
      return afghan(v, edit, v2);
    }


    // console.log(v, v2);
    const plate = (!isValidIran(v, v2) && !isValidAfghan(v, v2)) ? v2 : v;
    if (!isValidIran(plate, v2) && !isValidAfghan(plate, v2)) {
      // return `<span>${plate}</span>`;
      return `
      <span style="
        background-image: url(/img/${pickImage(0, 0, 0, 1)});
        background-size: contain;
        padding-left: 15px;
        font-weight: bold;
        max-width: fit-content;
        font-size: 16px;
        padding-top: 9px;
        padding-bottom: 8px;
        padding-right: 30px;
        background-position: center;
        position: relative;
        min-width: 120px;
      " class="d-flex flex-row-reverse align-center" style="justify-content: flex-end">
        <span style="margin-top: 2px; padding-right: 2px;">
         ${v2 ?? v}
        </span>
      </span>
    `;

    }


    if (v && (v.match(/\d/g) || []).length === 5) {
      const digits = v.replace(/\D/g, '');
      const part1 = digits.substring(0, 2);
      let part2 = digits.substring(2);
      part2 = part2.padEnd(5, "?");
      v = part1 + 'ein' + part2;
    } else if (v2 && (v2.match(/\d/g) || []).length === 5) {
      const digits = v2.replace(/\D/g, '');
      const part1 = digits.substring(0, 2);
      let part2 = digits.substring(2);
      part2 = part2.padEnd(5, "?");
      v2 = part1 + 'ein' + part2;
    }


    // const st = converto(plate.substring(2, plate.length - 5));
    // const sub = plate.substring(2, plate.length - 5);
    const sub = v.substring(2, v.length - 5);
    const isAlpha = /^[A-Za-z]+$/.test(sub);
    let st;
    if (isAlpha) {
      st = converto(sub);
    } else {
      st = "ع";
    }

    const ein = `<span style="height: 17px; margin-bottom: 3px; font-size: 14px; margin-right:3px; margin-left:4px">${st}</span>`;

    let part1 = highlightDifferences(v?.substring(0, 2) ?? '', v2?.substring(0, 2) ?? '');
    let part2 = highlightDifferences(v?.substring(v.length - 5, v.length - 2) ?? '', v2?.substring(v2.length - 5, v2.length - 2) ?? '');
    let part3 = highlightDifferences(v?.substring(v.length - 2) ?? '', v2?.substring(v2.length - 2) ?? '');
    // var part1 = highlightDifferences(v2?.substring(0, 2) ?? '', v?.substring(0, 2) ?? '');
    // var part2 = highlightDifferences(v2?.substring(v2.length - 5, v2.length - 2) ?? '', v?.substring(v.length - 5, v.length - 2) ?? '');
    // var part3 = highlightDifferences(v2?.substring(v2.length - 2) ?? '', v?.substring(v.length - 2) ?? '');
    if (noplace) {
      part1 = v?.substring(0, 2) ?? ''
      part2 = v?.substring(v.length - 5, v.length - 2) ?? ''
      part3 = v?.substring(v.length - 2) ?? ''
    }
    return `
      <span style="
        background-image: url(/img/${pickImage(edit, st, (noplace || (!v && v2)))});
        background-size: contain;
        padding-left: 15px;
        font-weight: bold;
        max-width: fit-content;
        font-size: 16px;
        padding-top: 9px;
        padding-bottom: 8px;
        padding-right: 30px;
        background-position: center;
        position: relative;
        min-width: 120px;
      " class="d-flex flex-row-reverse align-center" style="justify-content: flex-end">
        <span style="margin-top: 2px; padding-right: 2px;">
          ${part1}
        </span>
        ${ein}
        <span style="padding-left: 2px; margin-top: 2px;">
          <span style="
            font-size: 14px;
            margin-top: 6px;
            right: 0px;
            top: 9px;
            width: 24px;
            position: absolute;
            text-align: center;">
            ${part3}
          </span>
          ${part2}
        </span>
      </span>
    `;
  } catch (e) {
    console.error('Error formatting plate:', e);
    return '';
  }
}

function isValidIran(v, v2) {
  const p = v ?? v2 ?? "";
  const cleaned = p.replace(/\s/g, "");

  return /^(\d{2}[a-zA-Z]+?\d{5})$/.test(cleaned) || /^\d{7}$/.test(cleaned) || /^\d{5}$/.test(cleaned);
}

function isValidAfghan(v, v2) {
  const p = v2 ?? v ?? "";
  return /^[A-Z]{3},\d{1,5},[A-Z]$/.test(p); // مثلاً: KBL,12345,L
}

function highlightDifferences(str, ref) {
  if (!ref) return str;
  if (!str) return ref;
  let out = '';
  for (let i = 0; i < str.length; i++) {
    const char = str[i];
    const refChar = ref[i] ?? null;
    if (refChar && refChar !== '?' && char !== refChar) {
      out += `<span style="color:red">${refChar}</span>`;
    } else {
      out += char;
    }
  }
  return out;
}

// تابع تبدیل کد به کاراکتر فارسی
export function converto(t) {
  const $data = {
    'ein': 'ع',
    'ع': 'ein',
    'ta': 'ط',
    'ط': 'ta',
    'n': 'ن',
    'ن': 'n',
    'alef': 'الف',
    'لف': 'alef',
    'v': 'و',
    'و': 'v',
    'sad': 'ص',
    'ص': 'sad',
    'q': 'ق',
    'ق': 'q',
    'l': 'ل',
    'ل': 'l',
    's': 'س',
    'س': 's',
    'y': 'ی',
    'ی': 'y',
    'h': 'ه',
    'ه': 'h',
    'd': 'د',
    'د': 'd',
    'm': 'م',
    'م': 'm',
    'b': 'ب',
    'ب': 'b',
  };
  return $data[t] ?? t
  switch (t) {
    case 'ein': return 'ع';
    case 'ta': return 'ط';
    case 'n': return 'ن';
    case 'alef': return 'الف';
    case 'v': return 'و';
    case 'sad': return 'ص';
    case 'q': return 'ق';
    case 'l': return 'ل';
    case 's': return 'س';
    case 'y': return 'ی';
    case 'h': return 'ه';
    case 'd': return 'د';
    case 'm': return 'م';
    case 'b': return 'ب';
    default: return t;
  }
}

// تابع انتخاب تصویر پس‌زمینه بسته به وضعیت
export function pickImage(edit, st, miss, Kham = false) {
  // if (Kham) return 'kham.png';
  if (miss) return 'pelakCyan.png';
  if (edit) return 'pelakGreen.png';
  if (st === 'ع') return 'pelak.png';
  // return 'plateDefault.svg';
  return 'pelakNormal.png'

}

// تابع فرمت افغان باکمی تمیزکاری
export function afghan(v, edit, v2 = null) {
  v2 = v2?.replace(/ein/g, '').replace(/[^a-zA-Z0-9,]/g, '');

  function highlightDifferences(str, ref) {
    if (!ref) return str;
    if (!str) return ref;
    let out = '';
    for (let i = 0; i < Math.max(str.length, ref.length); i++) {
      const char = str[i];
      const refChar = ref[i] ?? null;
      if (refChar && refChar !== '?' && char !== refChar) {
        out += `<span style="color:red">${refChar}</span>`;
      } else {
        out += char;
      }
    }
    return out;
  }

  const split = v ? v.split(',') : [];
  const split2 = v2 ? v2.split(',') : [];
  let get_city = '';
  let get_leter = '';
  let get_city2 = '';
  let get_leter2 = '';
  let number = '';
  let number2 = '';

  if (split.length === 1) {
    number = split[0] ?? '';
  } else {
    get_city = getCity(split[0]);
    get_leter = getLeter(split[2]);
    number = split[1] ?? '';
  }

  if (split2.length === 1) {
    number2 = split2[0] ?? '';
  } else {
    get_city2 = getCity(split2[0]);
    get_leter2 = getLeter(split2[2]);
    number2 = split2[1] ?? '';
  }

  let image = '/img/afghan.png';
  if (edit) image = '/img/afghanGreen.png';
  if (!v && v2) image = '/img/afghanCyan.png';

  return `
    <span style="
      background-image: url(${image});
      background-size: contain;
      font-weight: bold;
      max-width: fit-content;
      font-size: 16px;
      padding-top: 12px;
      padding-bottom: 12px;
      background-position: center;
      position: relative;
      min-width: 120px;
    " class="d-flex flex-row-reverse align-center" style="justify-content: flex-end">
      <span style="
        left: 8px;
        position: absolute;
        justify-content: space-between;
        margin-top: 2px;
        padding-right: 2px;
        font-size: 10px;
        display: flex;
        flex-direction: column;
        height: 66%;
        text-align: center;
      ">
        <span>${get_city[1] ?? ''}</span>
        <span>${highlightDifferences(get_city[0] ?? '', get_city2[0] ?? '')}</span>
      </span>
      <span style="
        display: flex;
        flex-direction: column;
        width: 100%;
        justify-content: center;
        align-items: center;
        margin-left: 13px;
      ">
        <span>${highlightDifferences(number, number2)}</span>
        <span style="font-family: sans-serif;">${highlightDifferences(number, number2)}</span>
      </span>
      <span style="
        right: 10px;
        position: absolute;
        justify-content: space-between;
        margin-top: 2px;
        padding-right: 2px;
        font-size: 12px;
        display: flex;
        flex-direction: column;
        height: 57%;
        text-align: center;
      ">
        <span>${get_leter[1] ?? ''}</span>
        <span>${highlightDifferences(get_leter[0] ?? '', get_leter2[0] ?? '')}</span>
      </span>
    </span>
  `;
}

// توابع کمکی افغان
export function getCity(i) {
  const map = {
    BDG: ['BDG', 'بادغیس'],
    BAG: ['BAG', 'بغلان'],
    LGM: ['LGM', 'لغمان'],
    BLH: ['BLH', 'بلخ'],
    LGR: ['LGR', 'لوگر'],
    BAM: ['BAM', 'بامیان'],
    NGR: ['NGR', 'ننگرهار'],
    DYK: ['DYK', 'دایکندی'],
    NRZ: ['NRZ', 'نیمروز'],
    NUR: ['NUR', 'نورستان'],
    FYB: ['FYB', 'فریاب'],
    PAK: ['PAK', 'پکتیا'],
    GAZ: ['GAZ', 'غزنی'],
    GHR: ['GHR', 'غور'],
    PJR: ['PJR', 'پنجیشیر'],
    PRN: ['PRN', 'پروان'],
    SAM: ['SAM', 'سمنگان'],
    JZJ: ['JZJ', 'جوزجان'],
    SRP: ['SRP', 'سرپل'],
    ORZ: ['ORZ', 'اروزگان'],
    KPS: ['KPS', 'کپیسا'],
    WDK: ['WDK', 'وردک'],
    PTA: ['PTA', 'پکیتا'],
    BDN: ['BDN', 'بدخشان'],
    KNR: ['KNR', 'کونر'],
    ZBL: ['ZBL', 'زابل'],
    NAZ: ['NAZ', 'نیمروز'],
    KDR: ['KDR', 'کندهار'],
    HRT: ['HRT', 'هرات'],
    HEL: ['HEL', 'هلمند'],
    KST: ['KST', 'خوست'],
    KDZ: ['KDZ', 'کندز'],
    FRH: ['FRH', 'فراه'],
    TAK: ['TAK', 'تخار'],
    LGH: ['LGH', 'تخار'],
    KBL: ['KBL', 'کابل'],
    LOG: ['LOG', 'لوگر'],
  };
  return map[i] || [i, '-'];
}

export function getLeter(i) {
  // فعلا فقط L پشتیبانی می‌شود
  switch (i) {
    case 'L': return ['L', 'ل'];
    default: return ['L', 'ل'];
  }
}
