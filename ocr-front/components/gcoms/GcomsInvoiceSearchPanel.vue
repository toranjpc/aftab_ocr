<template>
  <CardWidget title="ثبت اطلاعات بیجک گمرک">
    <div class="d-flex flex-row flex-wrap mb-2">
      <v-alert v-show="gcomsInvoice === null" dense class="w-100" color="error" dark>
        فاکتوری پیدا نشد
      </v-alert>

      <div v-if="gcomsInvoice" class="d-flex flex-wrap rounded-lg mb-1 w-100"
        style="border: 1px dashed rgba(0, 0, 0, 0.4)">
        <span class="mx-3">
          <span class="font-weight-bold">اسم شرکت:</span>
          {{ gcomsInvoice?.customer?.title }}
        </span>
        <span class="mx-3">
          <span class="font-weight-bold">وزن اعلامی:</span>
          {{ formatNumber(gcomsInvoice.weight - 0) }}
        </span>
        <span class="mx-3">
          <span class="font-weight-bold">خارج شده :</span>
          {{ formatNumber(sumWeight) }}
        </span>
        <span class="mx-3" :class="gcomsInvoice?.weight - sumWeight < 0 ? 'red--text' : ''">
          <span class="font-weight-bold">مانده :</span>
          {{ formatNumber(gcomsInvoice.weight - sumWeight) }}
        </span>
        <span class="mx-3">
          <span class="font-weight-bold">شماره کوتاژ :</span>
          {{ gcomsInvoice?.kutazh }}
        </span>
        <span class="mx-3">
          <span class="font-weight-bold">نوع کالا :</span>
          {{ getSafe(gcomsInvoice, 'gcoms_data.CommodityName', '---') }}
        </span>
      </div>
    </div>

    <DynamicForm v-model="form" :fields="fields" ref="dynamicForm" :edit-item="editItem" class="white rounded" />

    <v-card-actions>
      <v-spacer />
      <v-btn small elevation="0" color="success" @click="save">
        <v-icon small left>fal fa-check-circle</v-icon>
        <span>ذخیره</span>
      </v-btn>
    </v-card-actions>
  </CardWidget>
</template>

<script>
import { DynamicForm } from 'majra'
import { get as getSafe } from 'lodash'
import { formatNumber } from '@/helpers/invoiceHelpers'
import CardWidget from '~/components/widgets/CardWidget.vue'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default {
  components: { DynamicForm, CardWidget },

  props: {
    fields: {},
    activePlateNumber: {},
  },

  data() {
    return {
      form: {},
      alert: '',
      editItem: {},
      alertType: '',
      barcode: null,
      gcomsInvoice: '',
    }
  },

  computed: {
    customNb() {
      return this.form?.customNb
    },
    sumWeight() {
      return this.gcomsInvoice?.gcoms_out_data?.reduce(
        (sum, item) => sum + item.weight,
        0
      )
    },
  },

  created() {
    this._listen('searchInvoice', () => {
      this.searchInvoice(this.form.customNb)
    })
  },

  mounted() {
    window.addEventListener('keydown', this.handleKeydown)
  },

  methods: {
    getSafe,
    formatNumber,
    NormalizeVehicleNumberAsImg,

    performAction(event) {
      this.searchInvoice(this.form.customNb)

      document.getElementById('weight')?.focus()
    },

    handleKeydown(event) {
      if (event.key === 'Enter') {
        if (document.activeElement.id === 'customNb') {
          this.performAction()
          return 0
        } else if (document.activeElement.id === 'weight') {
          return 0
        }
      }
      const currentTime = new Date().getTime()

      if (currentTime - this.lastKeyTime > 100) {
        // اگر بیش از 100 میلی‌ثانیه از آخرین کلید گذشته باشد، بافر را خالی کنید
        this.buffer = ''
      }

      if (event.key !== 'Shift' && event.key !== 'Enter') {
        this.buffer += event.key
      }

      // اگر کلید Enter فشرده شده باشد
      if (event.key === 'Enter') {
        this.barcode = this.buffer // ذخیره بارکد در buffer
        this.buffer = ''
        if (this.barcode?.length === 16) {
          let x = this.barcode
          x = this.barcode.slice(6, 16)
          this.searchInvoice(x)
        }
      }

      this.lastKeyTime = currentTime
    },

    async searchInvoice(inputValue) {
      this._event('loading')

      inputValue = inputValue || this.form.customNb

      this.gcomsPayment = null

      if (!inputValue) {
        this._event('alert', {
          text: 'اطلاعات را وارد کنید',
          color: 'error',
        })
        return this._event('loading', false)
      }

      const res = await this.$axios.$get(
        `/invoice?_with=gcomsOutData&filters[kutazh][$eq]=${inputValue}`
      )

      this.gcomsInvoice = getSafe(res, 'Invoice.data[0]') || { weight: '' }

      // this.gcomsInvoice.weight = getSafe(res, 'Invoice.data', []).reduce(
      //   (acc, item) => acc + item.weight,
      //   0
      // )

      this.editItem = {
        ...this.form,
        customNb: getSafe(this.gcomsInvoice, 'customNb') || this.form.customNb,
      }

      this._event('loading', false)
    },

    validateInputs() {
      return this.$refs.dynamicForm.$refs.form.validate()
    },

    saveLog() {
      this.form.plate_number = this.activePlateNumber?.plate_number
      this.form.plate_type = this.activePlateNumber?.plate_type
      this.form.user_id = this.$auth.user.id
      this.form.invoice_id = this.gcomsInvoice.id
      this.$axios
        .$post('gcoms-out-data', {
          GcomsOutData: { ...this.form, gate: this.$route.params.gate_id },
        })
        .then((res) => {
          this._event('alert', {
            text: 'ثبت شد',
            color: 'success',
          })
          this.form = {}
          this.editItem = {}
          this.sumWeight = 0
          this.gcomsData = null
          this.gcomsInvoice = null
          this.activePlateNumber = null
          this._event('reloadOcrLogData')
        })
    },

    weightIsOver() {
      return this.gcomsInvoice?.weight - this.sumWeight < this.form.weight
    },

    setAlert() {
      let message = false

      if (!this.gcomsInvoice || !this.gcomsInvoice.id) {
        message = 'فاکتوری ثبت نکردید.'
        this.alertType = 'no_invoice'
        this.alert =
          'برای این پلاک، فاکتوری ثبت نشده است. آیا مایلید گزارش دهید و آن را به محوطه بازگردانید؟'
      } else if (this.weightIsOver()) {
        message = 'مقدار وزن باید کمتر باشد'
        this.alertType = 'overload'
        this.alert = `وزن وارد شده بیش از حد مجاز است و امکان خروج با این وزن وجود ندارد. حداکثر وزنی که می‌توانید ثبت کنید، ${formatNumber(
          this.gcomsInvoice?.weight - this.sumWeight
        )} تن است.`
      }

      if (!message) return true

      this._event('alert', {
        text: message,
        color: 'error',
      })

      this._event('gcoms.trucklog.report', {
        alert: this.alert,
        alertType: this.alertType,
        gcomsInvoiceId: this.gcomsInvoice?.id,
        overload:
          this.form.weight - (this.gcomsInvoice?.weight - this.sumWeight),
      })

      return false
    },

    save() {
      const isValid = this.validateInputs()

      if (!isValid) return

      const res = this.setAlert()

      if (!res) return

      this.saveLog()
    },
  },
}
</script>
