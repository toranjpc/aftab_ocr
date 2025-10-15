import { get as getSafe, debounce } from 'lodash'
import validations from '@/helpers/validations'
import truckHelpers from '@/helpers/truckHelper.js'
import { persianDate, persianDateGlobal } from '~/helpers/helpers'
import ConvertToImg from '@/helpers/NormalizeVehicleNumberAsImg.js'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'

const url = process.env.baseURL.replace("api/","")

const runDebounce = debounce((callback) => {
  callback()
}, 2000)

export function gcomsFields(val) {
  return [
    {
      title: 'شماره کوتاژ',
      field: 'customNb',
      type: 'text',
      isHeader: true,
      col: { md: 5 },
      props: {
        id: 'customNb',
        rules: [validations.required()],
        type: 'number',
        'item-text': 'searchName',
        'item-value': 'id',
      },
      events: {
        input: (value) => {
          if (!value) return

          runDebounce(() => val._event('searchInvoice'))
        },
      },
    },

    {
      title: 'وزن (تن)',
      field: 'weight',
      type: 'text',
      isHeader: true,
      col: { md: 3 },
      props: {
        id: 'weight',
        ref: 'weight',
        rules: [validations.required()],
        type: 'number',
      },
    },

    {
      title: 'تاریخ وزن کشی',
      field: 'full_scale_date',
      type: 'date',
      props: {
        rules: [validations.required()],
        format: 'YYYY-MM-DD',
        type: 'date',
        view: 'month',
      },
      inList(date) {
        return new Date(date).toLocaleString('fa-IR')
      },
      isHeader: true,
      col: { md: 4 },
    },
  ]
}

export default function (val) {
  return [
    {
      title: 'نوع پلاک',
      field: 'plate_type',
      type: 'text',
      inList(i) {
        switch (i) {
          case 'iran':
            return `<img src="/img/flag/ir.svg" style="max-height: 25px;border-radius: 3px;"/>`
          case 'europe':
            return `<img src="/img/flag/eu.svg" style="max-height: 25px;border-radius: 3px;"/>`
          case 'afghan':
            return `<img src="/img/flag/af.svg" style="max-height: 25px;border-radius: 3px;"/>`
          default:
            return `<img src="/img/flag/ir.svg" style="max-height: 25px;border-radius: 3px;"/>`
        }
      },
    },
    {
      title: 'شماره پلاک',
      field: 'plate_num',
      type: 'text',
      inList(v, form) {
        return NormalizeVehicleNumberAsImg(v || '', form.plate_type)
      },
    },

    {
      title: 'گیت',
      field: 'gate_number',
      type: 'text',
    },
    {
      title: 'کد کانتینر',
      field: 'container_code',
      type: 'text',
      inList(v) {
        if (v) {
          return NormalizeContainerCodeAsImg(v)
        }
        return '-'
      },
    },
    {
      title: 'تصویر پلاک',
      field: 'plate_img',
      type: 'text',
      inList(item) {
        if (item)
          return (
            '<img style="border-radius:10px;margin-top:5px" src="data:image/png;base64, ' +
            item +
            '"/>'
          )
      },
    },
    {
      title: 'تصویر کانتینر',
      field: 'container_code_img',
      type: 'text',
      inList(item) {
        if (item)
          return (
            '<img style="border-radius:10px;margin-top:5px" src="data:image/png;base64, ' +
            item +
            '"/>'
          )
      },
    },
    {
      title: 'تاریخ',
      field: 'log_time',
      type: 'date',
      props: {
        type: 'dateTime',
      },
      inList(date) {
        return new Date(date).toLocaleString('fa-IR')
      },
    },
  ]
}

export const invoiceFields = [
  {
    field: 'invoice_number',
    title: 'شماره فاکتور',
  },
  {
    field: 'customer',
    title: 'صاحب کالا',
    convert: (item) => item?.title + ' ::: ' + item?.shenase_meli,
  },
  {
    field: 'pay_date',
    title: 'تاریخ فاکتور',
    convert: persianDate,
  },
  // {
  //   field: 'amount',
  //   title: 'هزینه پارکینگ',
  // },
]

export const bijacFields = [
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
    title: 'پلاک',
    field: 'plate',
    type: 'text',
  },
  {
        title: 'پلاک نرمال',
    field: 'plate_normal',
    type: 'text',
  },
  {
    title: 'شماره کانتینر',
    field: 'container_number',
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
  // {
  //   title: 'تعداد',
  //   field: 'pack_number',
  //   type: 'text',
  // },
  {
    title: 'نوع',
    field: 'type',
    type: 'text',
  },
  {
    title: 'سایز کانتیر',
    field: 'container_size',
    type: 'text',
  },
  {
    title: 'وضعیت خطرناک بودن',
    field: 'dangerous_code',
    type: 'select',
  },
  // {
  //   title: 'شناسه بیجک',
  //   field: 'exit_permission_iD',
  //   type: 'text',
  // },
]

export const truckFields = [
  {
    title: 'نوع پلاک',
    field: 'plate_type',

    inList(i) {
      switch (i) {
        case 'iran':
          return `<img src="/img/flag/ir.svg" style="max-height: 25px;border-radius: 3px;"/>`
        case 'europe':
          return `<img src="/img/flag/eu.svg" style="max-height: 25px;border-radius: 3px;"/>`
        case 'afghan':
          return `<img src="/img/flag/af.svg" style="max-height: 25px;border-radius: 3px;"/>`
        default:
          return `<img src="/img/flag/ir.svg" style="max-height: 25px;border-radius: 3px;"/>`
      }
    },
  },
  {
    title: 'شماره پلاک',
    field: 'plate_number',
    inList(v, form) {
      return ConvertToImg(v || '', form.plate_type)
    },
  },
  {
    title: 'تصویر پلاک',
    field: 'plate_image_url',
    inList(item) {
      if (item)
        return (
          '<img style="max-width: 100px;border-radius:10px;margin-top:5px" src="' +
          url +
          item +
          '"/>'
        )
    },
  },
  {
    title: 'کد کانتینر',
    field: 'container_code',
    inList(v, form) {
      const container = getSafe(form, 'parent.plate_image_url')

      if (container) {
        return NormalizeContainerCodeAsImg(container)
      }

      return '-'
    },
  },
  {
    title: 'تصویر',
    field: 'container_code_image_url',
    inList(_, form) {
      const container = getSafe(form, 'parent.vehicle_image_front_url')
      const front = getSafe(form, 'vehicle_image_front_url')
      const maxWidth = 'max-width: 100px;'

      const frontImg = front
        ? `<img class="resizable mx-1" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
          url +
          front +
          '"/>'
        : ''

      const backImg = container
        ? `<img class="resizable mx-1" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
          url +
          container +
          '"/>'
        : ''

      return frontImg + backImg
    },
  },
  {
    title: 'وضعیت',
    field: 'status',
    inList(item, row) {
      return truckHelpers.statusMessage(row)
    },
  },
]
