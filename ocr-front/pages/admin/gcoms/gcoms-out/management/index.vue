<template>
  <DynamicTemplate />
</template>

<script>
import fields from './fields'
import { DynamicTemplate } from 'majra'
import { getPermissions } from '~/helpers/helpers'

export default {
  components: { DynamicTemplate },

  layout: 'dashboard',

  beforeCreate() {
    const hiddenActions = getPermissions.call(this)
    hiddenActions.push('delete')
    hiddenActions.push('edit')
    hiddenActions.push('show')
    hiddenActions.push('printer')
    hiddenActions.push('download')
    hiddenActions.push('create')
    hiddenActions.push('filter')

    this.$majra.init({
      hiddenActions,
      mainRoute: {
        route: '/gcoms-out-not-permission?_with=gcomsInvoice.customer',
        key: 'GcomsOutNotPermission',
      },
      relations: [],
      fields: fields(this),
    })
  },
}
</script>
