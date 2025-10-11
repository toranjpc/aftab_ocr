import validations from '@/helpers/validations'
export default function (val) {
  return [
    {
      title: 'رمز فعلی',
      field: 'pass',
      type: 'text',
      props: {
        type: 'password',
        rules: [validations.required()],
      },
    },

    {
      title: 'رمز جدید',
      field: 'new_pass',
      type: 'text',
      props: {
        type: 'password',
        rules: [validations.required()],
      },
    },

    {
      title: 'تکرار رمز جدید',
      field: 'new_pass_rep',
      type: 'text',
      props: {
        type: 'password',
        rules: [validations.required()],
      },
    },


  ]
}
