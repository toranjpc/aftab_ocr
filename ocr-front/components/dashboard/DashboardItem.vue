<template>
  <v-tooltip
    :left="$vuetify.rtl"
    :right="!$vuetify.rtl"
    :disabled="$vuetify.breakpoint.smAndDown && !mini"
  >
    <template #activator="{ on, attrs }">
      <v-list-item
        exact
        dark
        active-class="active-class"
        :link="link"
        v-bind="attrs"
        :to="to"
        v-on="on"
        @click="fn"
      >
        <v-list-item-action>
          <i :class="'fal fa-' + icon"></i>
        </v-list-item-action>
        <v-list-item-content>
          <v-list-item-title v-text="title" />
        </v-list-item-content>
      </v-list-item>
    </template>
    <span v-text="title" />
  </v-tooltip>
</template>

<script>
export default {
  name: "DashboardItem",

  props: {
    title: {},
    icon: {},
    to: {},
    mini: {},
    link: {},
    fn: { default: () => () => {} },
  },
};
</script>

<style scoped>
.active-class {
  color: black !important;
}
</style>
