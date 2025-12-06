import { get as getSafe } from 'lodash'

const url = process.env.baseURL.replace("api/", "")

export default function (val) {
  return [
    {
      title: 'شماره پلاک',
      field: 'plate_number',
      type: 'text',
      isHeader: true
    },

    {
      title: 'تصویر پلاک',
      field: 'plate_image_url',
      type: 'text',
      inList(item) {
        const maxWidth = val.$vuetify.breakpoint.mobile
          ? 'max-width: 50px;'
          : 'max-width: 120px;'

        if (item)
          return (
            `<img class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
            url +
            item +
            '"/>'
          )
      },
      isHeader: true,
    },
    {
      title: 'تصویر روبرو',
      field: 'vehicle_image_front_url',
      type: 'text',
      inList(item) {
        const maxWidth = val.$vuetify.breakpoint.mobile
          ? 'max-width: 50px;'
          : 'max-width: 200px;'

        if (item)
          return (
            `<img class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
            url +
            item +
            '"/>'
          )
      },
      isHeader: true,
    },

    {
      title: 'کد کانتینر',
      field: 'container_code',
      type: 'text',
      isHeader: true
    },

    {
      title: 'تصویر کانتینر',
      field: 'container_code_image_url',
      type: 'text',
      inList(_, form) {
        const maxWidth = val.$vuetify.breakpoint.mobile
          ? 'max-width: 50px;'
          : 'max-width: 100px;'

        const item = getSafe(form, 'container_code_image_url')

        if (item)
          return (
            `<img class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
            url +
            item +
            '"/>'
          )
      },
      isHeader: true,
    },

    {
      title: 'تصویر پشت',
      field: 'vehicle_image_back_url',
      type: 'text',
      inList(item) {
        const maxWidth = val.$vuetify.breakpoint.mobile
          ? 'max-width: 50px;'
          : 'max-width: 200px;'

        if (item)
          return (
            `<img class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
            url +
            item +
            '"/>'
          )
      },
      isHeader: true,
    },

    {
      title: 'کالای خطرناک',
      field: 'IMDG',
      type: 'select',
      // inList(_, form) {
      //   let imdg = getSafe(form, 'IMDG')

      //   if (imdg > 0)
      //     return (
      //       `<div class="v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--small red">
      //        خطرناک
      //     </div>`
      //     )

      //   return (
      //     `<div class="v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--small green">
      //        غیر خطرناک
      //     </div>`
      //   )
      // },
      isHeader: true,
      values: [
        { text: 'خطرناک (AI)', value: 'danger_AI' },
        { text: 'خطرناک (بیجک)', value: 'danger_Bijac' },
        { text: 'غیر خطرناک (AI)', value: 'no_danger_AI' },
        { text: 'غیر خطرناک (بیجک)', value: 'no_danger_Bijac' },
      ],
    },
    {
      title: 'پلمپ',
      field: 'seal',
      type: 'text',
      inList(_, form) {
        return getSafe(form, 'seal')
      },
      isHeader: true,
    },

    {
      // title: 'کوتاژ / وزن',
      title: 'سایز / وزن',
      field: 'weight_customNb',
      type: 'text',
      isHeader: true,
    },

    {
      title: 'بیجک / تردد',
      field: 'ocr_bijac',
      type: 'text',
      isHeader: true,
    },

    // {
    //   title: 'تعداد tu',
    //   field: 'ocr_tu',
    //   type: 'text',
    //   isHeader: true,
    // },

    {
      title: 'تاریخ لاگ',
      field: 'log_time',
      type: 'date',
      props: {
        type: 'dateTime',
      },
      inList(date) {
        return `<div style="direction:ltr">${new Date(date).toLocaleString('fa-IR')}</div>`
      },
      isHeader: true,
    },

    {
      title: 'وضعیت',
      field: 'match_status',
      type: 'select',
      rel: false,
      values: [
        // { text: 'دو فاکتور متفاوت', value: 'bad_match_nok' },
        { text: 'کانتینر بدون فاکتور', value: 'container_ccs_nok' },
        { text: 'فاکتور (کانتینر)', value: 'container_ccs_ok' },
        { text: 'کانتینر بدون بیجک', value: 'container_without_bijac' },
        { text: 'پلاک بدون بیجک', value: 'plate_without_bijac' },
        { text: 'فاکتور فله', value: 'gcoms_ok' },
        { text: 'فله بدون فاکتور', value: 'gcoms_nok' },
        { text: 'پلاک بدون فاکتور', value: 'plate_ccs_nok' },
        { text: 'فاکتور (پلاک)', value: 'plate_ccs_ok' },
        { text: 'فاکتور (ccs)', value: 'ccs_ok' },
        { text: 'بدون فاکتور (css)', value: 'ccs_nok' },
      ],

      // inList(data) {
      //   const list = {
      //     bad_match_nok: ['دو فاکتور متفاوت', 'purple'],
      //     bad_plate_read_ok: ['فاکتور (کانتینر)', 'green'],
      //     container_without_bijac: ['بدون بیجک', 'orange'],
      //     no_bijac: ['بدون بیجک', 'orange'],
      //     gcoms_ok: ['فاکتور', 'cyan'],
      //     gcoms_nok: ['بدون فاکتور', 'red'],
      //     miss_container_ccs_ok: ['فاکتور (پلاک)', 'green'],
      //     miss_container_ccs_nok: ['بدون فاکتور', 'red'],
      //     bad_container_read_ok: ['فاکتور (پلاک)', 'lightgreen'],
      //     ccs_ok: ['فاکتور', 'darkgreen'],
      //     ccs_nok: ['بدون فاکتور', 'red'],
      //   }

      //   return `<div class="pa-2 white--text rounded-lg " style="background: ${getSafe(
      //     list,
      //     data + '[1]',
      //     '-'
      //   )}">${getSafe(list, data + '[0]', '-')}<div>`
      // },
      isHeader: true,
    },

    {
      title: '',
      field: 'plate_number_2',
      type: 'hidden',
    },
  ]
}
