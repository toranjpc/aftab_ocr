import validations from '@/helpers/validations'
export default function (val) {
  return [
    {
      title: 'نام فایل',
      field: 'name',
      type: 'text',
      isHeader: true,
      props: {
        rules: [validations.required()],
      },
    },

    {
      title: 'نام بانک',
      field: 'bank_maqsad',
      type: 'hidden',
      rel: false,
      isHeader: true,
      default: 'out',
    },

    {
      title: 'فایل',
      field: 'document_url',
      uploadPath: process.env.uploadPath,
      type: 'file',
      // fileType: 'img',
      isHeader: true,
      // type: 'uploadbox',
      fileType: 'img',
      isImage: true,
      // multiple: true,
    },

    {
      title: 'تمامی رکورد ها',
      field: 'total',
      type: 'text',
      isHeader: true,
      showIn: ['show'],
    },

    {
      title: 'رکوردهای اضافه شده',
      field: 'news',
      type: 'text',
      isHeader: true,
      showIn: ['show'],
    },
  ]
}
