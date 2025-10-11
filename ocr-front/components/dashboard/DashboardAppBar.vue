<template>
  <v-app-bar
    absolute
    app
    flat
    class="mt-4 px-xs-0 px-lg-1 mx-xs-0 mx-lg-2"
    :clipped-left="$vuetify.rtl"
  >
    <v-btn
      v-if="$vuetify.breakpoint.xs || !rightDrawer"
      icon
      color="blueLogo"
      @click.stop="$emit('changeRightDrawer', !rightDrawer)"
    >
      <v-icon size="40">mdi-menu</v-icon>
    </v-btn>

    <span v-else class="me-5">
      <v-btn
        v-if="!$vuetify.breakpoint.xs"
        icon
        color="blueLogo"
		style="background-color:#e9f0f7"
        @click="$emit('changeMini', !mini)"
      >
        <v-icon size="40" v-if="!mini">{{
          $vuetify.rtl ? 'mdi-chevron-right' : 'mdi-chevron-left'
        }}</v-icon>
        <v-icon size="40" v-else>
          {{ !$vuetify.rtl ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon
        >
      </v-btn>
    </span>

    <Clock />

    <v-spacer />

    <UserMenu />
  </v-app-bar>
</template>

<script>
import { get as getSafe } from 'lodash'
import UserMenu from '~/components/dashboard/DashboardAppBarUserMenu'
import Clock from '@/components/utilities/Clock.vue'

export default {
  name: 'DashboardAppBar',

  components: {
    UserMenu,
    Clock,
  },

  props: {
    rightDrawer: { type: Boolean, default: true },
    mini: { type: Boolean, default: true },
  },

  methods: { getSafe },
}
</script>
