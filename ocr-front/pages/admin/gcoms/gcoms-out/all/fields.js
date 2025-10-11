import validations from '@/helpers/validations'
import ConvertToImg from '@/helpers/NormalizeVehicleNumberAsImg.js'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import { persianDateGlobal } from '~/helpers/helpers'

export default function (val) {
  return [
    {
      title: 'کاربر',
      field: 'user',
      sendKey: 'user_id',
      type: 'select',
      rel: {
        model: 'User',
      },
      props: {
        'item-text': 'name',
        'item-value': 'id',
      },
      isHeader: true,
    },

    {
      title: 'کوتاژ',
      field: 'customNb',
      type: 'text',
      isHeader: true,
    },

    {
      title: 'پلاک',
      field: 'plate_number',
      type: 'text',
      inList(v, form) {
        console.log(v)
        return ConvertToImg(v || '', form.plate_type)
      },
      isHeader: true,
    },

    {
      title: 'نوع پلاک',
      field: 'plate_type',
      type: 'text',
      isHeader: true,
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
      title: 'وزن',
      field: 'weight',
      type: 'text',
      isHeader: true,
    },

    {
      title: 'تاریخ وزن کشی',
      field: 'full_scale_date',
      type: 'date',
      isHeader: true,
    },

    {
      title: 'تاریخ ثبت',
      field: 'created_at',
      type: 'date',
      props: {
        type: 'datetime',
        format: 'YYYY/mm/dd HH:mm',
      },
      isHeader: true,
      inList(item) {
        return persianDateGlobal(item, 'dateTime')
      },
    },
  ]
}
