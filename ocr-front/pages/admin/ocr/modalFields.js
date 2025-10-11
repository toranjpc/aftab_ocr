import { get as getSafe } from 'lodash'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'

const url = process.env.baseURL

export default function (val) {
  return [
    {
      title: 'شماره پلاک',
      field: 'plate_number',
      type: 'text',
      inList(v, form) {

        let concat = ''

        return (
          NormalizeVehicleNumberAsImg(
            form.plate_number_edit || v,
            form.plate_type,
            !!form.plate_number_edit,
            form.plate_number_3
          ) + concat
        )
      },
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
            `<img loading="lazy" class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
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
            `<img loading="lazy" class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
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
      inList(v, form) {

        let concat = ''

        // if (form.container_code_2 && form.container_code_2 != v && !form.container_code_3)
        //   concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2, '#2957a4', form.container_code_3)

        // if (form.container_code_edit || v) {
          return (
            NormalizeContainerCodeAsImg(
              form.container_code_edit || v || null,
              form.container_code_edit ? 'green' : '#2957a4',
              form.container_code_3
            ) + concat
          )
        // }

        if (form.container_code_3) {
          return (
            NormalizeContainerCodeAsImg(
              form.container_code_3,
              '#2aa2db',
            )
          )
        }

        return '-'
      },
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
            `<img loading="lazy" class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
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
            `<img loading="lazy" class="resizable" style="border-radius:10px;margin-top:5px;${maxWidth}" src="` +
            url +
            item +
            '"/>'
          )
      },
      isHeader: true,
    },

    // {
    //   title: 'کالای خطرناک',
    //   field: 'IMDG',
    //   type: 'text',
    //   inList(_, form) {
    //     let imdg = getSafe(form, 'IMDG')

    //     if (imdg > 0)
    //       return (
    //         `<div class="v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--small red">
    //          خطرناک
    //       </div>`
    //       )

    //     return (
    //       `<div class="v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--small green">
    //          غیر خطرناک
    //       </div>`
    //     )
    //   },
    //   isHeader: true,
    // },

    // {
    //   title: 'پلمپ',
    //   field: 'seal',
    //   type: 'text',
    //   inList(_, form) {
    //     return getSafe(form, 'seal')
    //   },
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
    }
  ]
}
