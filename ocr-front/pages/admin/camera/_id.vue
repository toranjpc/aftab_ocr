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

    var matchGate = this.$route.params.id || 0;

    this.$majra.init({
      hiddenActions,
      fields: fields(this),
      mainRoute: { route: `/camera/?group=${matchGate}`, key: 'Camera' },
      formData: { group: matchGate }
    })
  },
}
</script>
