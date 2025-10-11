import dashboardItems from './dashboardItems'

const extraPermissions = {
  'admin-station-station-invoice': [
    { key: '.invoice', icon: 'receipt', tooltip: 'قبض' },
    { key: '.factor', icon: 'file-invoice-dollar', tooltip: 'فاکتور' },
    {
      key: '.expertFields',
      icon: 'money-check-pen',
      tooltip: ' کارشناس سکو',
    },
  ],
  "admin-pay-slip-show": [
    { key: '.all', icon: 'receipt', tooltip: 'همه' },
  ]
}

const standard = (items) => {
  return items.map((i) => {
    i.name = i.text
    i.id = Math.random() * Math.random()
    if ('to' in i) {
      i.key = i.to.slice(1).split('/').join('-').split('.')
    }
    if ('children' in i) {
      i.children = standard(i.children)
    }
    return i
  })
}

const out = standard(dashboardItems)

const flats = []

function addColors(node) {
  if (!('children' in node) || node.children.length == 0) {
    flats.push(node)
    node.children = []
    node.children.push({
      id: node.key + '.show',
      key: node.key + '.show',
      icon: 'fal fa-eye',
      color: 'info',
      name: node.name + ' | نمایش',
      tooltip: 'نمایش',
    })
    node.children.push({
      id: node.key + '.create',
      key: node.key + '.create',
      icon: 'fal fa-plus',
      color: 'success',
      name: node.name + ' | ایجاد',
      tooltip: 'ایجاد',
    })
    node.children.push({
      id: node.key + '.edit',
      key: node.key + '.edit',
      icon: 'fal fa-edit',
      color: 'primary',
      name: node.name + ' | ویرایش',
      tooltip: 'ویرایش',
    })
    node.children.push({
      id: node.key + '.delete',
      key: node.key + '.delete',
      icon: 'fal fa-trash',
      color: 'error',
      name: node.name + ' | حذف',
      tooltip: 'حذف',
    })
    if (extraPermissions[node.key]) {
      for (const item of extraPermissions[node.key])
        node.children.push({
          id: node.key + item.key,
          key: node.key + item.key,
          icon: 'fal fa-' + item.icon,
          color: 'info',
          name: node.name + '| ' + item.tooltip,
          tooltip: item.tooltip,
        })
    }
    return
  } else {
    for (const child of node.children) addColors(child)
  }
}

out.forEach((t) => addColors(t))

export default out

export { flats }
