<template><div></div></template>

<script>
export default {
  name: "DashboardPushSubscriber",

  mounted() {
    this.newPush();
    if ('BroadcastChannel' in window) {
      const bc = new BroadcastChannel('notif_channel')
      setInterval(() => {
        bc.postMessage(true)
      }, 100)
    }
  },

  methods: {
    newPush() {
      function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
        const base64 = (base64String + padding)
          .replace(/\-/g, '+')
          .replace(/_/g, '/')

        const rawData = window.atob(base64)
        const outputArray = new Uint8Array(rawData.length)

        for (let i = 0; i < rawData.length; ++i) {
          outputArray[i] = rawData.charCodeAt(i)
        }
        return outputArray
      }

      const configurePushSub = () => {
        if (!('serviceWorker' in navigator)) {
          return
        }

        let reg
        navigator.serviceWorker.ready
          .then((swreg) => {
            reg = swreg
            return swreg.pushManager.getSubscription()
          })
          .then((sub) => {
            if (sub === null) {
              const vapidPublicKey =
                'BKX1DlBNWYVo-kKZv1mknRZAKOnMCPsLJL_j1hBNcDzYYHtG15KF4AA8KuQIgeMoSLBEcLc7jS7zWAaqdqrN1EA'
              const convertedVapidPublicKey =
                urlBase64ToUint8Array(vapidPublicKey)
              return reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: convertedVapidPublicKey,
              })
            }
          })
          .then((newSub) => {
            if (!newSub) return
            // const db = require('@/helpers/storage')
            const token = this.$auth.getToken('local').split(' ')[1]
            // db.set('token', token)
            return fetch(this.$axios.defaults.baseURL + '/push-subscription', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                Authorization: 'bearer ' + token,
              },
              body: JSON.stringify(newSub),
            })
          })
      }

      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/menuman-sw.js').then(() => {
          Notification.requestPermission().then(() => configurePushSub())
        })
      }
    },
  }
}
</script>
