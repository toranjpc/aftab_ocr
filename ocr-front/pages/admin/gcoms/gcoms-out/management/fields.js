import { get as getSafe } from 'lodash'
import validations from '@/helpers/validations'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default function (val) {
  return [
    {
      title: 'شماره فاکتور',
      field: 'gcomsinvoice',
      type: 'text',
      rel: false,
      isHeader: true,
      default: 'out',
      inList(v) {
        return v?.invoice_number ?? '-'
      },
    },
    {
      title: 'نوع مشکل',
      field: 'type',
      type: 'text',
      isHeader: true,
      props: {
        rules: [validations.required()],
      },
      inList(status) {
        const values = [
          { text: 'اضافه بار', value: 'overload' },
          { text: 'بدون فاکتور', value: 'no_invoice' },
        ]
        switch (status) {
          case 'overload':
            return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  blueLogo  white--text">${values[0].text}</span></div>`
          case 'no_invoice':
            return `<div class="justify-center۳ d-flex"><span class="px-2 py-1 text-center rounded-xl text-caption  teal white--text">${values[1].text}</span></div>`
          default:
            break
        }
        // return map[status]
      },
    },
    {
      title: 'اضافه بار',
      field: 'overload',
      type: 'text',
      rel: false,
      isHeader: true,
      default: 'out',
      inList(v) {
        return v?.toLocaleString('fa-IR') ?? '-'
      },
    },
    {
      title: 'شماره پلاک',
      field: 'plate_number',
      type: 'text',
      isHeader: true,
      inList(v, form) {
        return NormalizeVehicleNumberAsImg(v || '', form.plate_type)
      },
    },
    {
      title: 'اطلاعات فاکتور',
      field: 'gcoms_invoice',
      type: 'text',
      isHeader: true,
      inList(data) {
        const fields = {
          customNb: 'شماره پروانه: ',
          receipt_number: 'قبض انبار: ',
          sazman_date: 'تاریخ صدور فاکتور: ',
          sazman_invoice_number: 'شماره فاکتور: ',
        }

        let out = ''

        for (const key in fields) {
          out += ' ::: ' + getSafe(fields, key) + getSafe(data, key)
        }

        return (
          data?.customer?.title + ' ::: ' + data?.customer?.shenase_meli + out
        )
      },
    },
  ]
}
