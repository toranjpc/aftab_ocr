import Vue from 'vue'

export default {
  required(message) {

    message = message || 'وارد کردن این مورد الزامی است'
    return (value) => {
      return (!!value && value !== '' && value !== null) || message
    }
  },

  isInt(message) {
    message = message || 'عدد وارد شده باید صحیح باشد'
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      const x = parseFloat(value)

      const isInt = !isNaN(value) && (x | 0) === x

      return isInt || message
    }
  },

  max(max) {
    max = max || 15
    const message = 'حداکثر کاراکتر مجاز ' + max + 'می باشد'
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (value && value.length <= max) || message
    }
  },

  min(min) {
    min = min || 3
    const message = 'حداقل کاراکتر مجاز ' + min + ' می باشد '
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (value && value.length >= min) || message
    }
  },

  email(message) {
    message = message || 'ایمیل وارد شده صحیح نمی باشد.'
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return /.+@.+\..+/.test(value) || message
    }
  },

  between(min = 5, max = 5) {
    const message = 'کاراکتر وارد شده باید بین ' + min + ' و ' + max + ' باشد '
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (value && value.length >= min && value.length <= max) || message
    }
  },

  digits(number = 11, message = 'نعداد اعداد وارد شده صحیح نمی باشد') {
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (value && value.length === number) || message
    }
  },

  isDigit(message = 'تنها میتوانید عدد وارد کنید') {
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (value && !isNaN(value)) || message
    }
  },

  isBiggerThan(number = 0) {
    const message = 'عدد باید بزرگتر از ' + number + ' باشد '
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (!isNaN(value) && value >= number) || message
    }
  },

  phone() {
    const message = 'تلفن وارد شده معتبر نمی باشد'
    return this.digits(11, message)
  },

  numBetween(one, two) {
    const message = 'عدد وارد شده باید بین ' + one + ' و ' + two + ' باشد'
    return (value) => {
      if ([undefined, null, ''].includes(value)) return true

      return (+value >= one && +value <= two) || message
    }
  },

  file: {
    types: {
      image: ['png', 'jpg', 'jpeg', 'gif', 'pdf'],
      video: ['mp4', 'wmv', 'mkv', 'flv', 'mov', '3gp'],
    },
    start(fn) {
      try {
        fn()
        return true
      } catch (err) {
        Vue._event('alert', {
          text: err,
          color: 'error',
        })
        return false
      }
    },
    sizeLessThan(file, size) {
      const message = 'حجم فایل باید کمتر از ' + size + ' MB باشد '
      const result = file.size / 1000000 <= size
      if (!result) {
        throw message
      }
    },
    type(file, types = [], message) {
      const defaultMessage =
        'فایل با فرمت  ' + types.join(' , ') + ' قابل قبول است '

      message = message || defaultMessage

      const result = types.includes(file.type.split('/')[1])

      if (!result) {
        throw message
      }
    },
  },

  getValidation(name, args) {
    return this[name](args)
  },
  checkEconomicCode() {
    const message = 'کد اقتصادی صحیح نمی باشد'
    return (v) => {
      if (!v || v == null) return message
    }
  },
  checkMelliID() {
    const message = 'کد اقتصادی صحیح نمی باشد'
    return (v) => {
      if (!!v && v !== '' && v !== null) return

      if (v.length === 11 || v.length === 10) return

      return message
    }
  },

  checkPostalCode() {
    const message = 'کد پستی صحیح نمی باشد'
    return (v) => {
      if (!(!!v && v !== '' && v !== null)) return message
      if (v.length !== 10) return message
    }
    // return (v) => {
    //   if (!(!!v && v !== '' && v !== null)) return message
    //   v = v + ''
    //   if (!v.match(/\b(?!(\d)\1{3})[13-9]{4}[1346-9][013-9]{5}\b/gm))
    //     return message
    // }
  },

  checkMelliCode() {
    const message = 'کد ملی صحیح نمی باشد'
    return (v) => {
      if (!(!!v && v !== '' && v !== null)) return message
      if (v.length === 10) {
        if (
          v == '1111111111' ||
          v == '0000000000' ||
          v == '2222222222' ||
          v == '3333333333' ||
          v == '4444444444' ||
          v == '5555555555' ||
          v == '6666666666' ||
          v == '7777777777' ||
          v == '8888888888' ||
          v == '9999999999'
        ) {
          return message
        }
        const c = parseInt(v.charAt(9))
        const n =
          parseInt(v.charAt(0)) * 10 +
          parseInt(v.charAt(1)) * 9 +
          parseInt(v.charAt(2)) * 8 +
          parseInt(v.charAt(3)) * 7 +
          parseInt(v.charAt(4)) * 6 +
          parseInt(v.charAt(5)) * 5 +
          parseInt(v.charAt(6)) * 4 +
          parseInt(v.charAt(7)) * 3 +
          parseInt(v.charAt(8)) * 2
        const r = n - parseInt(n / 11) * 11
        if (
          (r === 0 && r === c) ||
          (r === 1 && c === 1) ||
          (r > 1 && c === 11 - r)
        ) {
          return true
        }
        return message
      } else {
        return false
      }
    }
  },
}
