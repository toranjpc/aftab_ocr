// const domain = 'https://ocrapi.add-app.ir/'

// const domain = 'http://127.0.0.1:8000/'
// const domain = 'http://bandar-api.test/'
// const domain = 'http://185.145.187.250:8080/'
// const domain = 'http://46.148.36.110:8080/'

// const domain = 'http://46.148.36.110:8000/ocrbackend/'
const domain = '/api/'
const cameraUrls = {
  "1": {
    title: "شرقی 1",
    gate_number: "1",
    camera: {
      camera_number: "1",
      ip: '46.148.36.110:8000',
      type:'plate'
    }
  },
  "2": {
    title: "غربی 1",
    gate_number: "1",
    camera: {
      camera_number: "1",
      ip: '46.148.36.110:8000',
      type:'plate'
    }
  },
  "3": {
    title: "غربی 2",
    gate_number: "1",
    camera: {
      camera_number: "1",
      ip: '46.148.36.110:8000',
      type:'plate'
    }
  },
  "4": {
    title: "غربی 3",
    gate_number: "1",
    camera: {
      camera_number: "1",
      ip: '46.148.36.110:8000',
      type:'plate'
    }
  },
}


export default {
  ssr: false,

  loadingIndicator: '~/static/html/loading.html',

  head: {
    title: 'سامانه آفتاب درخشان',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: '' },
    ],
    link: [
      { rel: 'shortcut icon', type: 'image/x-icon', href: '/icon.svg' },
      { rel: 'icon', type: 'image/x-icon', href: '/icon.svg' },
    ],
  },


  plugins: [
    {
      src: '@/plugins/vuetify',
      ssr: false,
    },
    {
      src: '@/plugins/vue-gates',
      ssr: false,
    },
    {
      src: '@/plugins/majra',
      ssr: false,
    },
    {
      src: '@/plugins/apexcharts',
    },
    {
      src: '@/plugins/sse.js'
    }
  ],

  css: [
    '@/static/css/main.css',
    '@/static/fonts/IRANSans/css/style.css',
    '@/static/fonts/fontawesome/6/css/all.css',
    '@static/css/btn.css',
    '@static/css/fafa-style.css',
  ],

  components: true,

  modules: ['@nuxtjs/axios', '@nuxtjs/auth', 'nuxt-leaflet'],

  buildModules: ['@nuxtjs/pwa'],

  axios: {
    baseURL: domain + 'api',
  },

  env: {
    baseURL: domain,
    uploadPath: domain + 'api/upload-file',
    posCallbackUrl: domain + 'api/callback',

    cameraUrls: cameraUrls
  },

  server: {
    host: '0.0.0.0',
    port: 8082,
  },

  auth: {
    strategies: {
      local: {
        endpoints: {
          login: {
            url: '/login',
            method: 'post',
            propertyName: 'access_token',
          },
          logout: {
            url: '/logout',
            method: 'post',
          },
          user: false,
          refresh: {
            url: '/refresh',
            method: 'post',
            propertyName: 'access_token',
          },
        },
        token: {
          maxAge: 60 * 60,
        },
        refreshToken: {
          maxAge: 20160 * 60,
        },
        autoFetchUser: false,
      },
    },
    redirect: {
      login: '/',
      home: '/admin/dashboard',
    },
  },

  router: {
    base: '/ocr/',
    trailingSlash: false,
  },

  build: {
    transpile: ['rtsp-relay']
  },

  //   serverMiddleware: [
  //   '~/serverMiddleware/rtsp-relay.js'
  // ]
}
