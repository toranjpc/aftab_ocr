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

    this.$majra.init({
      hiddenActions,
      mainRoute: { route: '/log-receiver', key: 'LogReceiver' },
      fields: fields(this),
    })
  },
}
</script>
