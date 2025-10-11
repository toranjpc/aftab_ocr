<template>
  <v-app id="inspire-4" :class="$vuetify.rtl ? 'set-iransas' : 'set-avenir'">
    <NavigationDrawer
      :mini="mini"
      :right-drawer="rightDrawer"
      :dashboard-items="dashboardItems"
      @changeRightDrawer="rightDrawer = $event"
    />

    <AppBar
      :right-drawer="rightDrawer"
      :mini="mini"
      @changeMini="mini = $event"
      @changeRightDrawer="rightDrawer = $event"
    />

    <v-main class="mx-3 mx-sm-5 mx-md-5 mx-lg-1 mt-6">
      <div class="px-xs-0 px-lg-1">
        <bread-crumbs class="mx-1 mb-5"></bread-crumbs>
        <div class="pa-xs-0 pa-md-1 pa-lg-3" style="max-width: 100%">
          <nuxt />
        </div>
      </div>
    </v-main>

    <Alert />

    <Loading />

    <AudioPanel />

    <!-- <LiveChat /> -->
  </v-app>
</template>

<script>
import { selectedLang } from 'majra'
import Alert from '~/components/utilities/Alert.vue'
import dashboardItems from '@/helpers/dashboardItems'
import Loading from '~/components/utilities/Loading.vue'
import AudioPanel from '@/components/utilities/AudioPanel'
import AppBar from '~/components/dashboard/DashboardAppBar'
import BreadCrumbs from '~/components/utilities/breadCrumbs.vue'
import NavigationDrawer from '~/components/dashboard/DashboardNavigationDrawer'

export default {
  name: 'Dashboard',

  components: {
    Alert,
    AppBar,
    Loading,
    AudioPanel,
    BreadCrumbs,
    NavigationDrawer,
  },

  middleware: ['fetchUser', 'auth', 'access', 'checkPass'],

  data() {
    return {
      mini: false,
      goDark: false,
      clipped: false,
      rightDrawer: '',
      dashboardItems,
      items: [],
    }
  },

  computed: {
    currentRouteName() {
      return this.$route.name
    },
  },

  created() {
    this._listen('mnav', ({ event, items }) => {
      this.items = items
    })
    selectedLang.lang = 'fa'
  },

  mounted() {
    this._event('templateMounted')
  },
}
</script>

<style>
.v-main__wrap {
  overflow-x: clip !important;
  overflow-y: clip !important;
}

.v-application {
  background-color: var(--v-mamadback-base) !important;
}

#__nuxt .text-start {
  text-align: right !important;
}

.v-application--is-rtl .v-main__wrap > div {
  direction: rtl;
}

.v-application--is-rtl .v-list-item__content {
  direction: rtl !important;
}

.v-list--nav .v-list-item {
  padding: 0 4px;
}

.v-data-table--dense > .v-data-table__wrapper > table > tbody > tr > td,
.v-data-table--dense > .v-data-table__wrapper > table > tbody > tr > th,
.v-data-table--dense > .v-data-table__wrapper > table > tfoot > tr > td,
.v-data-table--dense > .v-data-table__wrapper > table > tfoot > tr > th,
.v-data-table--dense > .v-data-table__wrapper > table > thead > tr > td,
.v-data-table--dense > .v-data-table__wrapper > table > thead > tr > th {
  height: 40px !important;
}

.nuxt-link-exact-active div,
.v-list-item--active {
  /* background-color: var(--v-liteBlueLogo-base) !important; */
  color: var(--v-blueLogo-base) !important;
}

#__layout
  #navigation
  .theme--light.v-list-item:not(.v-list-item--active):not(
    .v-list-item--disabled
  ) {
  color: #94a3b8 !important;
}

#__layout .v-application--is-rtl .sidebar-menu .v-list-item__action {
  margin-left: 3px !important;
}

.v-app-bar.v-toolbar.v-sheet {
  background-color: #ffffff00 !important;
  border-bottom: 1px solid #ffffff00 !important;
}

#__layout .v-application .secondary {
  background-color: var(--v-blueLogo-base) !important;
  border-color: var(--v-blueLogo-base) !important;
}

.mdi-magnify {
  color: var(--v-blueLogo-base) !important;
}

#__layout .v-application .primary {
  background-color: var(--v-yellowLogo-base) !important;
  border-color: var(--v-yellowLogo-base) !important;
}

.theme--dark.v-data-table,
.theme--dark.v-tabs .v-tabs-bar {
  background-color: var(--v-TableBar-base) !important;
}

.theme--dark.v-card,
.theme--dark.v-expansion-panels .v-expansion-panel {
  background-color: var(--v-cardTable-base) !important;
}

.theme--dark.v-list {
  background-color: var(--v-application-base) !important;
}

tbody tr:nth-of-type(odd) {
  background-color: var(--v-tableOdd-base) !important;
}

.theme--dark.v-data-table
  > .v-data-table__wrapper
  > table
  > tbody
  > tr:hover:not(.v-data-table__expanded__content):not(
    .v-data-table__empty-wrapper
  ) {
  background: var(--v-hoverTable-base) !important;
}

.theme--light.v-data-table .v-data-table__empty-wrapper {
  color: var(--v-dark-base) !important;
}

.theme--light th button {
  color: var(--v-dark-base) !important;
}

.theme--dark .v-data-table .v-data-table__wrapper table thead tr th,
.theme--dark.v-label,
.theme--dark th button,
.theme--dark.v-tabs > .v-tabs-bar .v-tab--disabled,
.theme--dark.v-tabs > .v-tabs-bar .v-tab:not(.v-tab--active),
.theme--dark.v-tabs > .v-tabs-bar .v-tab:not(.v-tab--active) > .v-btn,
.theme--dark.v-tabs > .v-tabs-bar .v-tab:not(.v-tab--active) > .v-icon,
.theme--dark.v-data-table .v-data-table__empty-wrapper {
  color: var(--v-white-base) !important;
}

.v-main__wrap::-webkit-scrollbar-thumb {
  background-color: var(--v-scroll-base) !important;
}

.theme--ligh .v-main__wrap::-webkit-scrollbar {
  background-color: var(--v-white-base) !important;
}

.theme--dark .v-main__wrap::-webkit-scrollbar {
  background-color: var(--v-blueLogo-base) !important;
  box-shadow: inset 0px 0px 6px var(--v-application-base);
}

.v-navigation-drawer,
.theme--dark.v-pagination .v-pagination__navigation,
.v-navigation-drawer,
.theme--dark.v-list {
  background-color: var(--v-navigation-base) !important;
}

.v-navigation-drawer {
  background-color: var(--v-navigation-base) !important;
}

body,
.v-snack__content,
#__nuxt .set-iransas .text-subtitle-2,
#__nuxt .set-iransas .v-btn__content,
#__nuxt .set-iransas .text-h1,
#__nuxt .set-iransas .text-h2,
#__nuxt .set-iransas .text-h3,
#__nuxt .set-iransas .text-h4,
#__nuxt .set-iransas .text-h5,
#__nuxt .set-iransas .text-h6,
#__nuxt .set-iransas .text-body-1,
#__nuxt .set-iransas .text-body-2,
#__nuxt .set-iransas .v-select.v-input--dense .v-select__selection--comma,
#__nuxt .set-iransas .v-messages__message,
#__nuxt .set-iransas .v-label,
#__nuxt .set-iransas .caption,
#__nuxt .set-iransas .text-subtitle-1,
#__nuxt .set-iransas .body-1,
#__nuxt .set-iransas .text-button,
#__nuxt .set-iransas .v-card__text,
#__nuxt .set-iransas .text-caption,
#__nuxt .set-iransas .text-overline,
#__nuxt .set-iransas .v-list-item__title,
#__nuxt .set-iransas .headline {
  font-family: 'IRANSans' !important;
  letter-spacing: 0 !important;
}

/*  */
.mamad-btn-sid-position {
  position: absolute !important;
  top: 7px;
}

.v-list-item__action {
  display: flex !important;
  justify-content: center;
}

/* .v-navigation-drawer__content {
  position: relative;
} */

.v-navigation-drawer__content,
.v-navigation-drawer,
nav {
  scrollbar-width: 0px !important;
  scrollbar-width: none !important;
}

.v-main__wrap {
  /* height: 94vh !important; */
}

.back-white {
  background: white !important;
}

/* mamad-speed-dial" */
.mamad-speed-dial {
  /* position: static !important; */
  left: 0 !important;
  top: 0 !important;
}

.mamad-speed-dial > .v-speed-dial__list > div {
  margin-top: 0px;
}

.mamad-speed-dial > .v-speed-dial__list {
  padding: 0;
  padding-top: 5px;
}

/* end */
/* for combobox size  */
.mamad-combo
  .v-select.v-select--chips:not(
    .v-text-field--single-line
  ).v-text-field--box.v-input--dense
  .v-select__selections,
.v-select.v-select--chips:not(
    .v-text-field--single-line
  ).v-text-field--enclosed.v-input--dense
  .v-select__selections {
  min-height: 35px !important;
}

.v-list-item__action {
  margin: 2px 0 !important;
  margin-left: 5px !important;
}

/* end */
.v-data-table > .v-data-table__wrapper > table > tbody > tr > td,
.v-data-table > .v-data-table__wrapper > table > tfoot > tr > td,
.v-data-table > .v-data-table__wrapper > table > thead > tr > td {
  font-size: 0.9rem !important;
}

th {
  font-size: 0.95rem !important;
}

th button {
  font-size: 0.95rem !important;
  color: #000 !important;
  font-weight: bold !important;
}

.theme--light.v-data-table {
  color: #000 !important;
}

.theme--light.v-data-table > .v-data-table__wrapper > table > thead > tr > th {
  color: #000 !important;
}

.v-breadcrumbs {
  padding: 11px 18px 0 0 !important;
}

.theme--light.v-breadcrumbs .v-breadcrumbs__divider,
.theme--light.v-breadcrumbs .v-breadcrumbs__item--disabled {
  color: #000 !important;
}

body::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #f5f5f5;
  width: 0px;
}

body::-webkit-scrollbar {
  width: 0px;
  background-color: #f5f5f5;
}

body::-webkit-scrollbar-thumb {
  background-color: var(--v-blueLogo-base);
}

@-moz-document url-prefix() {
  html,
  body {
    overflow: hidden !important;
  }
}

/* .v-navigation-drawer::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #f5f5f5;
  border-radius: 10px;
}

.v-navigation-drawer::-webkit-scrollbar {
  width: 0px;
  background-color: #f5f5f5;
}

.v-navigation-drawer::-webkit-scrollbar-thumb {
  border-radius: 0px;
  background-image: -webkit-gradient(
    linear,
    left bottom,
    left top,
    color-stop(0.44, rgb(122, 214, 217)),
    color-stop(0.72, rgb(73, 162, 189)),
    color-stop(0.86, rgb(31, 111, 126))
  );
} */
.v-navigation-drawer--mini-variant .sidebar-menu {
  padding: 8px 11px 8px 11px;
}

.v-main__wrap {
  /* max-height: 100vh; */
  overflow-y: scroll;
  overflow-x: hidden;
  direction: ltr;
}

.v-main__wrap::-webkit-scrollbar {
  width: 4px;
  box-shadow: inset 0px 0px 6px rgba(0, 0, 0, 0.3);
  box-shadow: inset 0px 0px 6px rgba(0, 0, 0, 0.3);
}

.v-main__wrap::-webkit-scrollbar-thumb {
  border-radius: 1px -100px 1px 1px;
}

.v-main__wrap {
  scrollbar-color: #627898 rgb(255, 255, 255) !important;
  scrollbar-shadow-color: inset 0px 0px 6px rgba(0, 0, 0, 0.3) !important;
  scrollbar-width: thin;
}

.v-navigation-drawer__content::-webkit-scrollbar {
  width: 0px;
}

.set-iransas .v-label {
  right: 0 !important;
  left: auto !important;
}

.set-avenir .v-label {
  left: 0 !important;
  right: auto !important;
}

.sidebar-menu .v-list-item__title {
  font-size: 12px !important;
  letter-spacing: 0.81px !important;
}

.sidebar-menu .v-list-item__action {
  margin-left: 0px !important;
  margin-right: 4px !important;
}

.sidebar-menu a {
  /* color: rgb(101 101 101 / 97%) !important; */
  text-decoration: none !important;
}

.sidebar-menu .v-list-item {
  margin-bottom: 0px !important;
}

.sidebar-menu .v-list-item__action i,
.sidebar-menu .v-list-item__icon i {
  font-weight: 100;
  font-size: 17px;
}

.sidebar-menu .v-list-item__icon {
  margin-left: 4px !important;
}

.v-navigation-drawer__content .primary--text {
  color: #fff !important;
  caret-color: #fff !important;
}

.v-application--is-rtl
  .v-list-group--no-action
  > .v-list-group__items
  > .v-list-item {
  padding-right: 25px;
}

.v-navigation-drawer__content .primary--text .test {
  font-weight: bold;
  color: var(--v-blueLogo-base) !important;
  text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.5) !important;
}

.v-text-field--filled:not(.v-text-field--single-line) input {
  margin-top: 4px !important;
}

.v-app-bar__nav-icon {
  margin-left: 5px !important;
}

.sid-icon-close {
  color: white;
  font-size: 24px;
  cursor: pointer;
}

button i:before {
  left: 0 !important;
}

td i:before {
  top: 1px !important;
}

.v-application--is-rtl
  .v-list-group--no-action.v-list-group--sub-group
  > .v-list-group__items
  > .v-list-item {
  padding-right: 61px !important;
}

.v-application--is-rtl
  .v-list--dense.v-list--nav
  .v-list-group--no-action
  > .v-list-group__items
  > .v-list-item {
  padding-right: 25px !important;
}

* {
  word-break: keep-all !important;
}

html {
  font-size: 14px !important;
}

#navigation
  .theme--light.v-list-item:not(.v-list-item--active):not(
    .v-list-item--disabled
  ) {
  color: antiquewhite !important;
}

#navigation
  .mr-2.v-list-item--active.v-list-item.v-list-item--link.theme--light {
  background-color: transparent !important;
  margin-top: 3px;
  color: var(--v-blueLogo-base) !important;
}

.theme--light.v-data-table > .v-data-table__wrapper > table > thead > tr > th {
  color: rgba(0, 0, 0, 0.856);
}

.v-pagination__navigation,
.v-pagination__item {
  box-shadow: 0 0 0 0 !important;
}

.v-pagination__item {
  font-size: 0.9rem;
  height: 24px;
  margin: 0.3rem;
  min-width: 24px;
}

.mamad-header {
  display: inline;
}

.mamad-header div {
  display: inline;
}

.v-main__wrap {
  overflow: auto;
}

.fixed-nav-btn {
  bottom: 0;
  position: fixed !important;
  border-radius: 0px !important;
  display: none !important;
  min-width: 56px !important;
  width: 100% !important;
}

@media screen and (max-height: 650px) {
  .fixed-nav-btn {
    display: block !important;
  }

  .sidebar-menu {
    padding-bottom: 30px !important;
    scroll-behavior: smooth !important;
  }
}

.cursor-pointer {
  cursor: pointer;
}

#menuleft .v-list-item {
  color: white !important;
}
</style>
