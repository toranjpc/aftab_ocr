import { EventSourcePolyfill } from 'event-source-polyfill';

export const persianDateGlobal = (date, mode = 'date') => {
  if (!date) return null

  date = new Date(date)
  const map = {
    date: date.toLocaleDateString('fa-IR'),
    dateTime:
      date.toLocaleTimeString('fa-IR') +
      ' , ' +
      date.toLocaleDateString('fa-IR'),
    time: date.toLocaleTimeString('fa-IR'),
  }

  return map[mode]
}

export const persianDate = (date) => {
  if (date) {
    return persianDateGlobal(date, 'date')
  }
  return null
}

export const isImage = (link) => {
  let links = Array.isArray(link) ? link : [link]
  links = links.filter((link) => !!link)

  if (!link || (links.length === 1 && typeof link !== 'string') || !isNaN(link))
    return false

  let res = false
  links.forEach((l) => {
    const types = ['.jpg', '.png', '.gif', 'jpeg']
    const match = l.match(/\.[0-9a-z]+$/)
    const fileType = match ? match[0] : 'noType'
    if (types.includes(fileType)) res = true
  })

  return res
}

export function getDays() {
  return [
    { text: 'شنبه', value: 'sat' },
    { text: 'یکشنبه', value: 'sun' },
    { text: 'دوشنبه', value: 'mon' },
    { text: 'سه شنبه', value: 'tue' },
    { text: 'چهارشنبه', value: 'wed' },
    { text: 'پنجشنبه', value: 'thu' },
    { text: 'جمعه', value: 'fri' },
  ]
}

export const groupFields = (fields, groupName) =>
  fields.map((f) => ({ ...f, group: groupName }))


export function goTo() {
  const mainWrapper = document.getElementsByClassName('v-main__wrap')

  mainWrapper[0].scrollTo({
    left: 0,
    top: 0,
    behavior: 'smooth',
  })
  // this.$vuetify.goTo(0);
}

export function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

export function getPermissions() {
  let hiddenActions = []
  if (!this.$auth.user.user_level.permission_do.includes('*')) {
    const filteredItems = this.$auth.user.user_level.permission_do.filter(
      (item) => item.startsWith(this.$route.name.split('___')[0] + '.')
    )

    hiddenActions = filteredItems.map((item) => item.split('.').pop())
    const all = ['delete', 'show', 'edit', 'create']
    hiddenActions = all.filter((item) => !hiddenActions.includes(item))
  }
  return hiddenActions
}

export function initSSE(url, resolve = () => { }, reject = () => { }) {
  if (window.sse && 'close' in window.sse) {
    window.sse.close()
  }

  window.sse = new EventSourcePolyfill(process.env.baseURL + url, {
    headers: {
      'Authorization': this.$auth.getToken('local')
    }
  })

  window.sse.onerror = function (event) {
    if (event.target.readyState === window.sse.CLOSED) {
      reject()
    }
  }

  window.sse.onmessage = ({ data }) => {
    if (['"ADD"', '"heart"'].includes(data)) return

    resolve(data)
  }

  // this._runAfterPageChanged(() => {
  //   window.sse && window.sse.close()
  // })
}

