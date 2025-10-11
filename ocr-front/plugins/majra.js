import Vue from 'vue'
import Majra from 'majra'

export default ({store, $axios}) => {
  Vue.use(Majra, {
    store,
    configs: {
      FILTER_URL: process.env.baseURL + 'api/filter',
      BASE_URL: process.env.baseURL,
      axios: {
        instance: $axios,
      },
    },
  })
}
