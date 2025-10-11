import connection from './osConnector'

const callbacks = {};

const send = (amount, url, callback = () => []) => {
  const key = 'pos-' + parseInt(Math.random() * 1000000)
  callbacks[key] = callback

  url = process.env.posCallbackUrl + url
  connection.socket.send(JSON.stringify({ event: 'pos', amount, url, key }))
}

const init = (eventBus, resolve = () => { }, reject = () => { }) => {
  connection.socket.onmessage = (event) => {
    eventBus('loading', false)

    const responseString = event.data
    const jsonString = responseString.substring(responseString.indexOf('{'))
    const jsonObject = JSON.parse(jsonString)

    if (jsonObject.resp !== 0) {
      return eventBus('alert', {
        color: 'error',
        text: 'تراکنش ناموفق',
      })
    }

    console.log(jsonObject)

    const key = jsonObject.key
    if (key) {
      callbacks[key](jsonObject)
      delete callbacks[key]
    }

    eventBus('alert', {
      color: 'success',
      text: 'تراکنش با موفقیت انجام شد',
    })

    resolve(jsonObject)
  }
}

export default {
  send,
  init,
}
