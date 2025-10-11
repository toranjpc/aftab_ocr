<template>
  <div class="d-flex flex-row flex-wrap align-start">
    <div class="col-12 col-md-7">
      <CardWidget title="کامیون های خوانش شده" class="mb-2">
        <template #actions>
          <div class="d-flex flex-row align-center justify-center">
            <v-btn color="success" @click="_event('showDialogChoosPlate')">
              افزودن پلاک دستی
            </v-btn>
            <v-text-field v-model="logsCount" class="mx-1 rounded" style="width: 100px" outlined dense
              label="تعداد ماشین ها" hide-details @input="getOcrMatches" />
          </div>
        </template>

        <div class="d-flex flex-row flex-wrap">
          <div class="d-none d-md-block col-6 col-md-4">
            <div class="rounded-lg fill-height" style="border: 1px dashed rgba(0, 0, 0, 0.4)">
              <CameraWidget gate="west_1" :plate="true" />
            </div>
          </div>

          <div v-for="item in ocrMatches" :key="item.id" class="pa-1 pa-md-2 col-6 col-md-4">
            <GcomsTruckLogItem :item="item" :activePlateNumber="activePlateNumber"
              @updateActivePlate="activePlateNumber = $event" />
          </div>
        </div>
      </CardWidget>
    </div>

    <div class="col-12 col-md-5">
      <GcomsInvoiceSearchPanel :fields="fields" :activePlateNumber="activePlateNumber" />
    </div>

    <GcomsTruckReportDialog :ocrMatches="ocrMatches" :activePlateNumber="activePlateNumber" />

    <GcomsChoosePlate @update="updatePlate" />
  </div>
</template>

<script>
import fields from './fields'
import { get as getSafe } from 'lodash'
import { initSSE } from '~/helpers/helpers'
import CardWidget from '~/components/widgets/CardWidget'
import CameraWidget from '~/components/widgets/CameraWidget.vue'
import GcomsChoosePlate from '~/components/gcoms/GcomsChoosePlate'
import GcomsTruckLogItem from '~/components/gcoms/GcomsTruckLogItem'
import GcomsTruckReportDialog from '~/components/gcoms/GcomsTruckReportDialog'
import GcomsInvoiceSearchPanel from '~/components/gcoms/GcomsInvoiceSearchPanel'

export default {
  layout: 'dashboard',

  components: {
    CardWidget,
    CameraWidget,
    GcomsChoosePlate,
    GcomsTruckLogItem,
    GcomsTruckReportDialog,
    GcomsInvoiceSearchPanel,
  },

  data() {
    return {
      ocrMatches: [],
      logsCount: 5,
      fields: fields(this),
      activePlateNumber: null,
    }
  },

  computed: {
    gateId() {
      return this.$route.params.gate_id
    },
  },

  created() {
    this.getOcrMatches()

    this._listen('reloadOcrMatchData', () => {
      this.getOcrMatches()
    })

    // initSSE.call(this, 'api/sse/ocr-match?receiver_id=' + this.gateId, () => {
    //   this.getOcrMatches()
    // })

    this.$sse.connect('api/sse/ocr-match?receiver_id=' + this.gateId, () => {
      this.getOcrMatches()
    });
  },

  methods: {
    setActivePlate(plate) {
      const { normalPlate, type } = plate

      this.activePlateNumber = {
        plate_number: normalPlate,
        plate_type: type,
      }
    },

    focusOn(field) {
      document.getElementById(field)?.focus()
      setTimeout(() => {
        document.getElementById(field)?.focus()
      }, 200)
    },

    async getMatchesPermission() {
      const ocrMatchResponse = await this.$axios.get(
        `/ocr-match?_doesnt_have=gcomsReport&sort=id:desc&filters[gate_number][$eq]=${this.gateId}&filters[$and][0][plate_number][$notNull]=true&filters[$and][1][valid_exit_gcoms][$eq]=0&filters[$and][1][container_code][$null]=0&itemPerPage=${this.logsCount}`
      )
      return getSafe(ocrMatchResponse, 'data.OcrMatch.data')
    },

    async getOcrMatches() {
      this._event('loading')
      try {
        const ocrMatchData = await this.getMatchesPermission()
        this.ocrMatches = ocrMatchData
        this._event('loading', false)
      } catch (error) {
        console.error('Error occurred:', error)
        this._event('loading', false)
      }
    },

    updatePlate(plate) {
      this.setActivePlate(plate)

      if (plate.isValid) this.focusOn('receipt_number')
    },
  },
}
</script>
