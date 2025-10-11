import Vue from 'vue'
import Vuetify from 'vuetify'
import fa from 'vuetify/es5/locale/fa'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/dist/vuetify.min.css'

Vue.use(Vuetify)

const opts = {
  rtl: true,

  iconfont: 'mdi',

  lang: {
    locales: { fa },
    current: 'fa',
  },

  theme: {
    options: {
      customProperties: true,
    },
    // light: false,
    // dark: true,
    themes: {
      light: {
        scroll: '#2957a4',
        yellowLogo: '#ffcc29',
        blueLogo: '#2957a4',
        grayLog: '#F3F3E8',
        liteBlueLogo: '#90AED9',
        mamadback: '#f1f5f9',
        link: '#f8f9fb',
        tableOdd: '#f4f4f4',
        active: '#f8f9fb',
        header: '#fff',
        navigation: '#fff',
        application: '#f8f9fb',
        blue: '#2e7ce4',
        indigo: '#6610f2',
        purple: '#6f42c1',
        pink: '#e83e8c',
        orange: '#fd7e14',
        yellow: '#f1bf43',
        green: '#08da82',
        teal: '#00c2b2',
        cyan: '#38b3d6',
        white: '#fff',
        gray: '#627898',
        'gray-dark': '#132843',
        primary: '#2e7ce4',
        secondary: '#627898',
        success: '#00c2b2',
        info: '#38b3d6',
        warning: '#f1bf43',
        danger: '#df3554',
        error: '#df3554',
        red: '#df3554',
        light: '#f8f9fa',
        dark: '#132843',
        mamadblue: '#09597b',
        mamadliteblue: '#ddf5fd',
        mamady: '#fdbd6a',
      },
      dark: {
        link: '#f8f9fb',
        tableOdd: '#272846',
        hoverTable: '383c58',
        active: '#515151',
        header: {
          base: '#272846',
          darken1: '#1b1c31',
        },
        navigation: '#1b1c31',
        application: '#272846',
        cardTable: '#292d4a',
        TableBar: '#292d4a',
        darkPapar: '#9ec3ef',
        scroll: '#de6f1e',
        //   blue: "#2e7ce4",
        //   indigo: "#6610f2",
        //   purple: "#6f42c1",
        //   pink: "#e83e8c",
        //   orange: "#fd7e14",
        //   yellow: "#f1bf43",
        //   green: "#08da82",
        //   teal: "#00c2b2",
        //   cyan: "#38b3d6",
        white: '#fff',
        //   gray: "#627898",
        //   "gray-dark": "#132843",
        //   primary: "#2e7ce4",
        secondary: '#de6f1e',
        //   success: "#00c2b2",
        //   info: "#38b3d6",
        //   warning: "#f1bf43",
        //   danger: "#df3554",
        //   error: "#df3554",
        //   red: "#df3554",
        //   light: "#f8f9fa",
        dark: '#132843',
      },
    },
  },
}

export default (ctx) => {
  const vuetify = new Vuetify(opts)
  ctx.app.vuetify = vuetify
  ctx.$vuetify = vuetify.framework
}

// {
//   ,
//   VApp,
//   VBtn,
//   VAppBar,
//   VAutocomplete,
//   VAvatar,
//   VBadge,
//   VBreadCrumbs,
//   VCard,
//   VChip,
//   VCheckbox,
//   VCombobox,
//   VData,
//   VDialog,
//   VDataTabel,
//   VExpansionPanel,
//   VDivider,
//   VFileInput,
//   VForm,
//   VGrid,
//   VImg,
//   VMain,
//   VMenu,
//   VNavigationDrawer,
//   VOverlay,
//   VProgressLinear,
//   VRadioGroup,
//   VRating,
//   VSelect,
//   VSnackbar,
//   VSheet,
//   VSpeedDial,
//   VStepper,
//   VSwitch,
//   VTabs,
//   VTextarea,
//   VTextField,
//   VToolbar,
//   VTooltip
// }
