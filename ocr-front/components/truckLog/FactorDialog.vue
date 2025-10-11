<template>
  <v-dialog v-model="dialog" max-width="80%">
    <v-card id="dialog">
      <v-card-title>
        <span ref="mockTitle">جزییات فاکتور</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-4 d-flex flex-row flex-wrap">
        <v-col cols="4">
          <CardWidget title="Gate Ocr">
            <div class="d-flex flex-column">
              <div v-for="field in logFields" :key="field.field" class="d-flex flex-row">
                <span v-if="field.title" class="ml-2">{{ field.title }}:</span>
                <span v-html="showLogField(field)" />
              </div>
            </div>
          </CardWidget>
        </v-col>

        <v-col cols="4">
          <CardWidget>
            <template #title>
              <div class="d-flex align-center">
                <span>بیجک‌ها</span>
                <v-chip class="ma-2" size="small">
                  {{ log.bijacs.length }}
                </v-chip>
              </div>
            </template>
            <template #actions>
              <v-btn v-for="bijacItem in sortBy(log.bijacs, 'bijac_number')" small
                :color="selectedBijac.id === bijacItem.id ? 'info' : 'primary'" class="black--text ma-1"
                @click="selectedBijac = bijacItem" :key="bijacItem.id">
                {{ bijacItem.receipt_number }}
              </v-btn>
            </template>
            <div class="d-flex flex-column">
              <div v-for="field in bijacFields" :key="field.field" class="d-flex flex-row">
                <span class="ml-2">{{ field.title }}:</span>
                <span>
                  {{
                    'convert' in field
                      ? field.convert(getSafe(selectedBijac, field.field))
                      : getSafe(selectedBijac, field.field)
                  }}
                </span>
              </div>
            </div>
          </CardWidget>
        </v-col>

        <v-col cols="4">
          <CardWidget>
            <template #title>
              <div class="d-flex align-center">
                <span>فاکتورهای صادر شده</span>
                <v-chip append-icon="mdi-cake-variant" class="ma-2" size="small" variant="outlined">
                  {{ log.invoices?.length || 0 }}
                </v-chip>
              </div>
            </template>
            <template #actions>
              <v-btn v-for="invoice in log.invoices" small
                :color="selectedInvoice.id === invoice.id ? 'info' : 'primary'" class="black--text ma-1"
                @click="selectedInvoice = invoice" :key="invoice.id">
                {{ invoice.invoice_number }}
              </v-btn>
            </template>
            <div class="d-flex flex-column">
              <div v-for="field in invoiceFields" :key="field.field" class="d-flex flex-row">
                <span class="ml-2">{{ field.title }}:</span>
                <span>
                  {{
                    // 'convert' in field
                    // ? field.convert(getSafe(selectedInvoice, field.field))
                    // : getSafe(selectedInvoice, field.field)

                    'inList' in field
                      ? field.inList(getSafe(selectedInvoice, field.field), selectedInvoice)
                      : getSafe(selectedInvoice, field.field)
                  }}
                </span>
              </div>
            </div>
          </CardWidget>
        </v-col>

        <v-col cols="12" v-if="selectedBijac.type === 'ccs'">
          <CardWidget title="کانتینر ها">
            <table class="col-12">
              <thead>
                <tr>
                  <td v-for="field in containerFields" :key="field.field">
                    {{ field.field }}
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="container in getSafe(log, 'invoice.containers', [])" :key="container.id"
                  :class="{ green: checkIsThisContainer(container) }">
                  <td v-for="field in containerFields" :key="field.field">
                    {{ getSafe(container, field.field, '-') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </CardWidget>
        </v-col>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { get as getSafe, sortBy } from 'lodash'
import logFields from '@/pages/admin/ocr/logFields'
import { persianDateGlobal } from '~/helpers/helpers'
import CardWidget from '@/components/widgets/CardWidget.vue'
import ContainerValidator from '@/helpers/ContainerValidator.js'

export default {
  components: {
    CardWidget,
  },

  data() {
    return {
      log: {},
      dialog: false,
      logFields: logFields(this),
      selectedBijac: {},
      selectedInvoice: {},
      invoiceFields: [
        {
          field: 'invoice_number',
          type: 'text',
          title: 'شماره فاکتور',
        },
        {
          field: 'pay_date',
          // field: 'request_date',
          type: 'date',
          title: 'تاریخ فاکتور',
          inList(date, item) {
            return item.request_date ?
              persianDateGlobal(item.request_date, 'dateTime') :
              persianDateGlobal(date, 'dateTime')
          },
        },
        {
          field: 'receipt_number',
          type: 'text',
          title: 'شماره قبض انبار',
        },
        {
          field: 'weight',
          type: 'text',
          title: 'وزن',
        },
        {
          title: 'تعداد',
          field: 'number',
          type: 'text',
        },
        {
          field: 'customer',
          type: 'text',
          title: 'صاحب کالا',
          inList(customer, item) {
            return customer?.title
          },
        },
        {
          field: 'pay_trace',
          type: 'text',
          title: 'شماره پرداخت',
        },
        {
          field: 'amount',
          type: 'text',
          title: 'مبلغ',
        },
        {
          field: 'tax',
          type: 'text',
          title: 'مالیات',
        },
        {
          title: 'کوتاژ',
          field: 'kutazh',
          type: 'text',
        },

      ],
      bijacFields: [
        {
          title: 'تاریخ بیجک',
          field: 'bijac_date',
          type: 'text',
          convert(date) {
            return persianDateGlobal(date, 'dateTime')
          },
        },
        {
          title: 'شماره بیجک',
          field: 'bijac_number',
          type: 'text',
        },
        {
          title: 'قبض انبار',
          field: 'receipt_number',
          type: 'text',
        },
        {
          title: 'شماره وسیله نقلیه',
          field: 'plate',
          type: 'text',
        },
        {
          title: 'شماره وسیله نقلیه نرمال',
          field: 'plate_normal',
          type: 'text',
        },
        {
          title: 'شماره کانتینر',
          field: 'container_number',
          type: 'text',
        },
        {
          title: 'سایز کانتینر',
          field: 'container_size',
          type: 'text',
        },
        {
          title: 'حمل یکسره',
          field: 'is_single_carry',
          type: 'select',
          rel: false,
          values: [
            { text: 'یکسره', value: '1' },
            { text: 'غیر یکسره', value: '0' },
          ],
        },
        {
          title: 'وزن',
          field: 'gross_weight',
          type: 'text',
        },
        {
          title: 'تعداد',
          field: 'pack_number',
          type: 'text',
        },
        {
          title: 'وضعیت خطرناک بودن',
          field: 'dangerous_code',
          type: 'select',
        },
        {
          title: 'نوع داده',
          field: 'type',
          type: 'text',
        },
        {
          title: 'شناسه بیجک',
          field: 'exit_permission_iD',
          type: 'text',
        },
        {
          title: 'تعداد دفعات ابطال',
          field: 'ocr_matches_count',
          type: 'text',
        },
      ],
      containerFields: [
        {
          field: 'wareHouseReceiptID',
        },
        {
          field: 'ContainerNo',
        },
        {
          field: 'ContainerSize',
        },
        {
          field: 'ContainerType',
        },
        {
          field: 'NetWeight',
        },
        {
          field: 'TareWeight',
        },
        {
          field: 'FCLLCL',
        },
        {
          field: 'LoadTypeDes',
        },
        {
          field: 'TerminalID',
        },
        {
          field: 'Warehousing',
        },
        {
          field: 'Yard',
        },
        {
          field: 'ExitDate',
        },
      ],
    }
  },

  created() {
    this._listen('ccs.dialog', (log) => {
      this.log = log
      this.dialog = true
      this.selectedBijac = getSafe(this.log, 'bijacs[0]', {})
      this.selectedInvoice = getSafe(this.log, 'invoices[0]', {})
    })
  },

  methods: {
    sortBy,
    getSafe,
    validateContainerCode(code) {
      if (typeof code !== 'string') return

      const splited = code.split(',')

      return new ContainerValidator().isValid(splited[2] + splited[3])
    },
    checkIsThisContainer(container) {
      let ocrContainerCode1 = getSafe(this.log, 'container_code', '') || ''
      let ocrContainerCode2 = getSafe(this.log, 'container_code_2', '') || ''

      ocrContainerCode1 = ocrContainerCode1.split(',')[2] ?? 'notFound'
      ocrContainerCode2 = ocrContainerCode2.split(',')[2] ?? 'notFound'

      const containerNo = container.ContainerNo.replaceAll(' ', '')

      return (
        containerNo.includes(ocrContainerCode1) ||
        containerNo.includes(ocrContainerCode2)
      )
    },
    showLogField(field) {
      let res =
        'inList' in field
          ? field.inList(getSafe(this.log, field.field), this.log)
          : getSafe(this.log, field.field)

      // if (field.field === 'container_code') {
      //   const code = getSafe(this.log, field.field)

      //   if (!this.validateContainerCode(code)) {
      //     res = `<div class="red">${res}</div>`
      //   }
      // }

      return res
    },
  },
}
</script>

<style>
.container-code>div {
  color: #132b8c !important;
  text-shadow: 1px 1px 3px #061f83;
}
</style>
