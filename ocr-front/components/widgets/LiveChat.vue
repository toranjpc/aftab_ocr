<template>
  <div v-if="show">
    <script>
      var LHC_API = LHC_API || {}
      LHC_API.args = {
        mode: 'widget',
        lhc_base_url: '//help.add-app.ir/index.php/',
        wheight: 450,
        wwidth: 350,
        pheight: 520,
        pwidth: 500,
        leaveamessage: true,
        check_messages: false,
        lang: 'per/',
      }
      ;(function () {
        var po = document.createElement('script')
        po.type = 'text/javascript'
        po.setAttribute('crossorigin', 'anonymous')
        po.async = true
        var date = new Date()
        po.src =
          '//help.add-app.ir/design/defaulttheme/js/widgetv2/index.js?' +
          ('' + date.getFullYear() + date.getMonth() + date.getDate())
        var s = document.getElementsByTagName('script')[0]
        s.parentNode.insertBefore(po, s)
      })()
    </script>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show: false,
    }
  },

  created() {
    this._listen('templateMounted', () => {
      this.show = true
    })
  },
}
</script>
