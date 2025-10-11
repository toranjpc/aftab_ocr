<template>
  <v-dialog v-model="dialog" max-width="500">
    <v-card id="dialog">
      <v-card-title>
        <span>گزارش</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-4">
        <h3 class="font-weight-bold">{{ alert }}</h3>
        <span
          v-if="activePlateNumber"
          v-html="
            NormalizeVehicleNumberAsImg(
              activePlateNumber?.plate_number,
              activePlateNumber?.plate_type
            )
          "
        ></span>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn small class="px-6" color="error" @click="dialog = false">
          بستن
        </v-btn>
        <v-btn small class="px-6" color="success" @click="report">
          فرستادن
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default {
  props: {
    alert: {},
    overload: {},
    alertType: {},
    gcomsInvoiceId: null,
    activePlateNumber: {},
  },

  data() {
    return {
      dialog: false,
    }
  },

  created() {
    this._listen(
      'gcoms.trucklog.report',
      ({ alertType, alert, overload, gcomsInvoiceId }) => {
        this.dialog = true
        this.alert = alert
        this.alertType = alertType
        this.overload = overload
        this.gcomsInvoiceId = gcomsInvoiceId
      }
    )
  },

  methods: {
    NormalizeVehicleNumberAsImg,

    report() {
      const data = {
        ocr_match_id: this.activePlateNumber?.id,
        type: this.alertType,
        overload: this.overload,
        invoice_id: this.gcomsInvoiceId,
        plate_type: this.activePlateNumber?.plate_type,
        plate_number: this.activePlateNumber?.plate_number,
      }

      this.$axios
        .$post('gcoms-report', {
          GcomsReport: data,
        })
        .then((res) => {
          this.dialog = false
          this._event('reloadOcrMatchData')
        })
    },
  },
}
</script>
