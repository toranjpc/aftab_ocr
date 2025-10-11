import validations from '@/helpers/validations'

export default function (val) {
  return [
    {
      title: 'url',
      field: 'endpoint',
      type: 'text',
      isHeader: true,
      props: {
        rules: [validations.required()],
      },
    },
    {
      title: 'اولویت',
      field: 'order',
      type: 'text',
      isHeader: true,
    },
  ]
}
