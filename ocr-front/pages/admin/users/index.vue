<template>
  <DynamicTemplate />
</template>

<script>
import { DynamicTemplate } from 'majra'
import fields from './fields'
import { getPermissions } from '~/helpers/helpers'

export default {
  components: { DynamicTemplate },

  layout: 'dashboard',

  beforeCreate() {
    const hiddenActions = getPermissions.call(this)

    this.$majra.init({
      hiddenActions,
      mainRoute: '/user',
      relations: ['/user-level-permission'],
      fields: fields(this),
    })
  },

  methods: {
    filterItemsAndReturnPartAfterDot(items) {
      const filteredItems = items.filter((item) =>
        item.includes('admin-user-level-permission')
      )

      return filteredItems.map((item) => item.split('.').pop())
    },
  },
}
</script>
