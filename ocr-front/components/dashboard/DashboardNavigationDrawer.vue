<template>
  <v-navigation-drawer
    id="navigation"
    app
    color="white "
    elevation="1"
    :value="rightDrawer"
    :right="$vuetify.rtl"
    :permanent="!$vuetify.breakpoint.xs"
    :mini-variant="!$vuetify.breakpoint.xs && mini"
    mini-variant-width="70"
    @input="$emit('changeRightDrawer', $event)"
    @update:mini-variant="$emit('changeMini', $event)"
  >
    <div class="d-flex flex-column flex-wrap align-center justify-center pa-2">
      <div v-if="!mini" class="flex-column d-flex col-8">
        <v-img
          src="/white-logo.png"
          :max-width="!mini ? '100%' : '35%'"
          class="ma-0 mt-0"
          contain
        />
      </div>
      <v-img
        v-if="mini"
        src="/white-logo.png"
        :max-width="$vuetify.breakpoint.smAndDown ? '50%' : '100%'"
        class="ma-0 mt-0 mb-1"
        contain
      />
      <div class="mb-4" v-if="!mini">
        <h4
          class="text-subtitle-1 font-weight-bold text-center white px-3 py-1 d-flex align-center"
          style="border-radius: 1000px"
        >
          سامانه آفتاب درخشان
        </h4>

        <h4
          class="new-border text-subtitle-1 mt-2 font-weight-bold text-center text--gray px-3 py-1 justify-center d-flex align-center d-flex flex-column"
          style="border-radius: 1000px"
        >
          <!-- <v-icon color="light" small right>fas fa-user</v-icon> -->
          <span>{{ $auth.user.name }}</span>
          <span>{{ $auth.user.user_level.name }}</span>
        </h4>
      </div>
    </div>

    <v-list nav class="font-weight-bold mt-3 sidebar-menu">
      <!-- <v-list-item to="/admin/dashboard" link>
        <v-list-item-action>
          <i class="fal fa-tachometer-alt-slowest"></i>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title>داشبورد</v-list-item-title>
        </v-list-item-content>
      </v-list-item> -->

      <span v-for="(item, index) in dashboardItems" :key="index">
        <node-tree v-if="item.to === undefined" :top="item"></node-tree>

        <v-list-item
          v-if="item.to !== undefined"
          v-permission:any="standardPermission(item)"
          :to="item.to"
          link
        >
          <v-list-item-action>
            <i :class="'fal fa-' + item.icon"></i>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>{{ item.text }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </span>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import DashboardItem from './DashboardItem'
import NodeTree from '@/components/utilities/NodeTree'

export default {
  name: 'DashboardNavigationDrawer',

  components: { DashboardItem, NodeTree },

  props: ['mini', 'rightDrawer', 'dashboardItems'],

  methods: {
    standardPermission(url) {
      // return 'dashboard.show'
      if (url.link === false) return 0
      return (
        url.to
          .substr(url.to.indexOf('/') + 1)
          .split('/')
          .join('-') + '.show'
      )
    },
    goto(refName) {
      const element = this.$refs[refName].$el
      const y = -10
      const x = element.getBoundingClientRect().top + y
      this.$nextTick(() => {
        element.scrollIntoView({
          block: x > 100 ? 'center' : 'end',
          behavior: 'smooth',
        })
      })
    },
  },
}
</script>

<style>
.new-border {
  border-top: 1px solid rgba(255, 255, 255, 0.5);
  border-left: 1px solid rgba(255, 255, 255, 0.5);
  border-right: 1px solid rgba(255, 255, 255, 0.5);
}
</style>
