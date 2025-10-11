import dashboardItems from './dashboardItems'
export default () => {
  const permissions = dashboardItems
    .map((item) => {
      if (item.to === '/') return false
      let splitted = item.to.split('/')
      return {
        id:
          splitted[splitted.length - 1] == undefined
            ? item.to
            : splitted[splitted.length - 1],
        name: item.text,
        children: [
          {
            id:
              splitted[splitted.length - 1] == undefined
                ? item.to + '.show'
                : splitted[splitted.length - 1] + '.show',
            name: item.text + ' | ' + 'نمایش',
          },
          {
            id:
              splitted[splitted.length - 1] == undefined
                ? item.to + '.create'
                : splitted[splitted.length - 1] + '.create',
            name: item.text + ' | ' + 'ایجاد',
          },
          {
            id:
              splitted[splitted.length - 1] == undefined
                ? item.to + '.edit'
                : splitted[splitted.length - 1] + '.edit',
            name: item.text + ' | ' + 'ویرایش',
          },
          {
            id:
              splitted[splitted.length - 1] == undefined
                ? item.to + '.delete'
                : splitted[splitted.length - 1] + '.delete',
            name: item.text + ' | ' + 'حذف',
          },
        ],
      }
    })
    .filter((item) => !!item)

  //  flat
  const flats = []

  function flatting(node) {
    if (!('children' in node) || node.children.length === 0) {
      flats.push(node)
    } else {
      node.children.forEach((p) => flatting(p))
    }
  }

  permissions.forEach((p) => flatting(p))

  return permissions
}
