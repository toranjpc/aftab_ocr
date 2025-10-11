<template>
  <span>
    <DynamicTemplate />
  </span>
</template>

<script>
import { DynamicTemplate } from 'majra'
import fields from './fields'
import { getPermissions } from '~/helpers/helpers'

export default {
  components: { DynamicTemplate },

  layout: 'dashboard',

  data() {
    return {}
  },
  beforeCreate() {
    const hiddenActions = getPermissions.call(this)
    this.$majra.init({
      hiddenActions,
      mainRoute: {
        route: '/gcoms-out-data?_with=user,gcomsInvoice&filters[gate][$eq]=1',
        key: 'GcomsOutData',
      },
      relations: [],
      fields: fields(this),
    })
  },

  methods: {},
}
</script>
