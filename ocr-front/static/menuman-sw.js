self.addEventListener('push', (e) => {
  let flag = true
  const bc = new BroadcastChannel('notif_channel')
  bc.onmessage = () => {
    flag = false
  }
  setTimeout(() => {
    if (!flag) return
    const data = e.data.json()
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/logo.svg', // icon
    })
  }, 200)
})
