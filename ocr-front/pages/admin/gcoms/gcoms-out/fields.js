import validations from '@/helpers/validations'
import { debounce } from 'lodash'

const runDebounce = debounce((callback) => {
  callback()
}, 2000)

export default function (val) {
  return [
    // {
    //   title: 'سریال قبض',
    //   field: 'StoreReceiptItem',
    //   type: 'text',
    //   isHeader: true,
    //   col: { md: 6 },
    //   props: {
    //     type: 'number',
    //     'item-text': 'searchName',
    //     'item-value': 'id',
    //     'append-outer-icon': 'fal fa-search',
    //   },
    //   events: {
    //     'click:append-outer': () => {
    //       val._event('searchInvoice')
    //     },
    //   },
    // },
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
