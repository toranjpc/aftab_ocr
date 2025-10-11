export default function (val) {
  return [
    {
      title: 'نام و نام خانوادگی',
      field: 'name',
      type: 'text',
      isHeader: true,
      rules: ['required'],
      col: { md: 6 },
    },
    {
      title: 'نام کاربری',
      field: 'username',
      type: 'text',
      isHeader: true,
      rules: ['required'],
      col: { md: 6 },
    },
    {
      title: 'وضعیت کاربر',
      field: 'activated',
      type: 'select',
      values: [
        { text: 'فعال', value: '1' },
        { text: 'غیر فعال', value: '0' },
      ],
      rel: false,
      isHeader: true,
      default: '1',
      col: { md: 6 },
      showIn: ['edit', 'show'],
    },
    {
      title: 'رمز عبور',
      field: 'password',
      type: 'text',
      isHeader: false,
      rules: ['required'],
      col: { md: 6 },
      showIn: ['create'],
    },
    {
      title: 'نقش',
      field: 'user_level',
      type: 'select',
      rel: {
        model: 'UserLevelPermission',
      },
      props: {
        'item-text': 'name',
        'item-value': 'id',
      },
      isHeader: true,
      default: '',
      col: { md: 6 },
    },
  ]
}
