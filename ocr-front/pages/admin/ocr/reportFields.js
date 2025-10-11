import { debounce } from 'lodash'
import PlateField from '~/components/utilities/PlateField.vue'
import ContainerField from '~/components/utilities/ContainerField.vue'

const refetchCustomer = debounce((val, data) => {
  if (data == null || data.includes('-')) {
    return 0
  }
  let route = null
  if (isNaN(parseInt(data))) {
    route = '/customer?filters[title][$contains]=' + data
  } else {
    route = '/customer?filters[shenase_meli][$contains]=' + data
  }
  val.$store.dispatch('dynamic/midit', {
    relations: [
      {
        route: route,
        key: 'Customer',
      },
    ],
  })
}, 1000)

export default function (val) {
  return [
    {
      title: 'تاریخ شروع',
      field: 'start_log_date',
      type: 'date',
      props: {
        max: new Date(),
        type: 'datetime',
        'compact-time': true,
        format: 'YYYY/MM/DD HH:mm',
      },
      group: 'تاریخ',
      col: { md: 6 },
    },
    {
      title: 'تاریخ پایان',
      field: 'end_log_date',
      type: 'date',
      props: {
        max: new Date(),
        type: 'datetime',
        'compact-time': true,
        format: 'YYYY/MM/DD HH:mm',
      },
      group: 'تاریخ',
      col: { md: 6 },
      filterFormat(form) {
        return {
          log_time: {
            $between: [form.start_log_date, form.end_log_date],
          },
        }
      },
    },

    {
      title: 'گیت',
      field: 'gate_number',
      type: 'select',
      rel: false,
      values: [
        { text: 'گیت غربی', value: '1' },
        { text: 'گیت شرقی 1', value: '2' },
        { text: 'گیت شرقی 2', value: '3' },
        { text: 'گیت شرقی 3', value: '4' },
      ],
      props: {
        multiple: true,
        chips: true,
        deletableChips: true,
      },
      group: 'گیت و دوربین',
      col: { md: 6 },
      filterFormat(form) {
        return {
          gate_number: {
            $in: form.gate_number,
          },
        }
      },
    },
    {
      title: 'دوربین',
      field: 'camera_number',
      type: 'select',
      rel: false,
      values: [
        { text: '1', value: '1' },
        { text: '2', value: '2' },
        { text: '3', value: '3' },
        { text: '4', value: '4' },
      ],
      props: {
        multiple: true,
        chips: true,
        deletableChips: true,
      },
      group: 'گیت و دوربین',
      col: { md: 6 },
      filterFormat(form) {
        return {
          camera_number: {
            $in: form.camera_number,
          },
        }
      },
    },

    {
      title: 'نام شرکت',
      field: 'customer_id',
      sendKey: 'customer_id',
      type: 'select',
      rel: {
        model: 'Customer',
      },
      props: {
        'item-text': 'searchName',
        'item-value': 'id',
        multiple: true,
      },
      events: {
        'update:search-input': (data) => {
          refetchCustomer(val, data)
        },
      },
      group: 'فاکتور/بیجک',
      filterFormat(form) {
        return {
          bijacs: {
            invoice: {
              customer_id: {
                $in: form.customer_id,
              },
            },
          },
        }
      },
    },
    {
      title: 'شماره قبض انبار',
      field: 'receipt_number',
      type: 'text',
      group: 'فاکتور/بیجک',
      col: { md: 6 },
      filterFormat(form) {
        return {
          bijacs: {
            receipt_number: {
              $contains: form.receipt_number,
            },
          },
        }
      },
    },
    {
      title: 'نوع بار',
      field: 'load_type',
      type: 'select',
      rel: false,
      values: [
        { text: 'فله', value: 'gcoms' },
        { text: 'کانتینری', value: 'ccs' },
      ],
      props: {
        multiple: true,
        chips: true,
        deletableChips: true,
      },
      group: 'فاکتور/بیجک',
      col: { md: 6 },
      filterFormat(form) {
        return {
          bijacs: {
            type: {
              $in: form.load_type,
            },
          },
        }
      },
    },
    {
      title: 'وضعیت فاکتور',
      field: 'invoice_status',
      type: 'select',
      rel: false,
      values: [
        { text: 'پرداخت شده', value: 'payed' },
        { text: 'پرداخت نشده', value: 'not_payed' },
      ],
      props: {
        multiple: true,
        chips: true,
        deletableChips: true,
      },
      group: 'فاکتور/بیجک',
      col: { md: 6 },
    },
    {
      title: 'وضعیت بیجک',
      field: 'bijac_status',
      type: 'select',
      rel: false,
      values: [
        { text: 'دارای بیجک', value: 'has_bijac' },
        { text: 'بدون بیجک', value: 'has_not_bijac' },
      ],
      props: {
        multiple: true,
        chips: true,
        deletableChips: true,
      },
      group: 'فاکتور/بیجک',
      col: { md: 6 },
      filterFormat(form) {
        const key =
          form.bijac_status === 'has_bijac'
            ? {
                $notNull: true,
              }
            : {
                $null: true,
              }

        return {
          bijacs: {
            plate: {
              ...key,
            },
          },
        }
      },
    },

    {
      title: 'پلاک',
      field: 'plate_number',
      component: PlateField,
      filterFormat(form) {
        return {
          plate_number: {
            $eq: form?.plate_number?.plate_number,
          },
        }
      },
    },
    {
      title: 'کد کانتینر',
      field: 'container_code',
      component: ContainerField,
      filterFormat(form) {
        return {
          container_code: {
            $eq: form.container_code,
          },
        }
      },
    },
  ]
}
