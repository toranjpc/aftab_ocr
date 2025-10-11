import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'

const url = process.env.baseURL

export default function (val) {
  return [
    {
      title: 'شماره پلاک',
      field: 'plate_number',
      type: 'text',
      isHeader: true,
      inList(v, form) {
        let concat = ''
        let prefix = ''

        if (form.plate_number_edit && form.plate_number_edit)
          prefix =
            NormalizeVehicleNumberAsImg(
              form.plate_number_edit || '',
              form.plate_type
            ) + '</br>'

        if (form.plate_number_2 && form.plate_number_2 != v)
          concat =
            '</br>' +
            NormalizeVehicleNumberAsImg(
              form.plate_number_2 || '',
              form.plate_type
            )

        return (
          prefix +
          NormalizeVehicleNumberAsImg(v || '', form.plate_type) +
          concat
        )
      },
    },
    {
      title: 'درصد',
      field: 'ocr_accuracy',
      type: 'text',
      isHeader: true,
    },
    {
      title: 'کالای خطرناک',
      field: 'IMDG',
      type: 'text',
      isHeader: true,
    },
    {
      title: 'پلمپ',
      field: 'seal',
      type: 'text',
      isHeader: true,
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
      title: 'تصویر کانتینر',
      field: 'container_code_image_url',
      type: 'text',
      inList(item) {
        const maxWidth = val.$vuetify.breakpoint.mobile
          ? 'max-width: 50px;'
          : 'max-width: 100px;'

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
      isHeader: true,
      inList(v, form) {
        let concat = ''

        if (form.container_code_2 && form.container_code_2 != v)
          concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2)

        if (v) {
          return (
            NormalizeContainerCodeAsImg(
              form.container_code_edit || v,
              form.container_code_edit ? 'green' : '#2957a4'
            ) + concat
          )
        }

        return '-'
      },
    },
    // {
    //   title: 'تاریخ',
    //   field: 'log_time',
    //   type: 'date',
    //   props: {
    //     type: 'dateTime',
    //   },
    //   inList(date) {
    //     return new Date(date).toLocaleString('fa-IR')
    //   },
    //   isHeader: true,
    // },
    {
      title: 'تاریخ ذخیره',
      field: 'created_at',
      type: 'date',
      props: {
        type: 'dateTime',
      },
      inList(date) {
        return new Date(date).toLocaleString('fa-IR')
      },
      isHeader: true,
    },
    {
      title: 'تاریخ لاگ',
      field: 'log_time',
      type: 'date',
      props: {
        type: 'dateTime',
      },
      inList(date) {
        return new Date(date).toLocaleString('fa-IR')
      },
      isHeader: true,
    },

    {
      title: '',
      field: 'plate_number_2',
      type: 'hidden',
    },
  ]
}
