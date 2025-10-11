import dashboardItems from '~/helpers/dashboardItems'

const dashItems = dashboardItems

function findRoute(dItems, permission) {
  for (const item of dItems) {
    if ('children' in item && item.link === false) {
      const found = findRoute(item.children, permission)
      if (found) return found
    } else if ('to' in item) {
      const per = item?.to?.replaceAll('/', '-')?.replace('-', '')
      if (per === permission) return item
    }
  }

  return false
}

export default function (context) {
  const pers = context.$auth.user.user_level?.permission_do
    ? context.$auth.user.user_level.permission_do
    : []

  context.$gates.setPermissions(pers)

  const permission = context.route.fullPath
    .replaceAll('/', '-')
    .replace('-', '')

  if (permission.endsWith('-')) {
    permission = permission.slice(0, -1)
  }

  if (permission.startsWith('-')) {
    permission = permission.slice(1)
  }

  // if (context.route.name === 'admin-dashboard') return

  if (context.$gates.hasPermission(permission + '.show')) {
    return
  }

  if (context.route.name === 'admin-user-pass-change') {
    return
  }

  const firstPermission =
    context.$auth.user.user_level.permission_do[0].split('.')[0]

  const found = findRoute(dashItems, firstPermission)

  return context.redirect(found?.to)
}
