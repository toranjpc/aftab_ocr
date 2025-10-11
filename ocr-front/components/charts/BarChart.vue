<script>
import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Bar,

  mixins: [reactiveProp],

  props: ['options', 'chartData'],

  mounted() {
    if (document.getElementsByClassName('set-avenir')[0] == 'undefined') {
      window.Chart.defaults.global.defaultFontFamily = 'iransans'
    }
    window.Chart.defaults.global.defaultColor = 'red'
    this.renderChart(this.chartData, this.options)
  },
}
</script>
