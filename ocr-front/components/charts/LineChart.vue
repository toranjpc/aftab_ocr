<script>
import { Line, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Line,

  mixins: [reactiveProp],

  props: ['options', 'chartData'],

  mounted() {
    if (document.getElementsByClassName('set-avenir')[0] == 'undefined') {
      window.Chart.defaults.global.defaultFontFamily = 'iransans'
    }
    this.renderChart(this.chartData, this.options)
  },
}
</script>
