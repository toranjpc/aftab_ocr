import validations from '@/helpers/validations'

export default function (val) {
  return [
    {
      title: 'نام دوربین',
      field: 'name',
      type: 'text',
      isHeader: true,
      props: {
        rules: [validations.required()],
      },
    },

    {
      title: 'برند دوربین',
      field: 'camera_brand',
      type: 'text',
      isHeader: true,
    },

    {
      title: 'main stream',
      field: 'main_stream',
      type: 'text',
    },

    {
      title: 'نوع دوربین',
      field: 'type',
      type: 'select',
      rel: false,
      values: [
        { text: 'پلاک خوان', value: 'plate' },
        { text: 'کانتینر خوان', value: 'container' },
        { text: 'تشخیص چهره', value: 'face' },
        { text: 'نظارتی', value: 'other' },
      ],
      isHeader: true,
      props: {
        rules: [validations.required()],
      },
    },

    {
      title: 'دسته بندی',
      field: 'group',
      type: 'text',
      isHeader: true,
      props: {
        rules: [validations.required()],
        readonly: true,
      },
    },

    {
      title: 'لینک استریم',
      field: 'stream',
      type: 'text',
      isHeader: true,
    },
    {
      title: 'آی پی',
      field: 'ip',
      type: 'text',
      isHeader: true,
    },
    {
      title: 'Username',
      field: 'username',
      type: 'text',
      isHeader: false,
      cols: { md: 6 },
    },
    {
      title: 'Password',
      field: 'password',
      type: 'text',
      isHeader: false,
      cols: { md: 6 },
    },
    {
      title: 'توضیحات',
      field: 'description',
      type: 'textarea',
      isHeader: true,
    },

    {
      title: 'وضعیت',
      field: 'active',
      type: 'select',
      rel: false,
      values: [
        { text: 'فعال', value: 1 },
        { text: 'غیرفعال', value: 0 },
      ],
      isHeader: true,
    },
  ]
}
