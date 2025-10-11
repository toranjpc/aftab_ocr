<template>
  <v-card class="elevation-0 transparent">
    <div v-if="loading['OcrLog']" style="
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        background-color: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
      " class="d-flex align-center justify-center">
      <i class="fas fa-spinner fa-spin fa-2x"></i>
    </div>



    <div class="d-flex flex-row">
      <v-img src="/img/manage/cctv.png" max-width="45px" class="mx-auto mb-1" max-height="90px" contain></v-img>
      <v-sheet class="mx-auto" style="background: none !important;">
        <v-slide-group multiple show-arrows>
          <v-slide-item v-for="truck in getItemsWithKey('OcrLog')" :key="truck.id">
            <div @click="$emit('select', truck)" style="max-width: 3%; position: relative; cursor: pointer">
              <img :style="'border: 3px solid ' + statusColor(truck)"
                style="border-radius: 4px; margin-top: 5px; max-width: 97%"
                :src="url + truck.vehicle_image_front_url" />
              <img style="
            border-radius:2px;
            margin-top: 5px;
            max-width: 37%;
            position: absolute;
            right: 5px;
            bottom: 15px;
            border: 1px solid white;
          " :src="url + truck.plate_image_url" />
            </div>
          </v-slide-item>
        </v-slide-group>
      </v-sheet>
      <v-img src="/img/manage/exit.png" max-width="45px" class="mx-auto mb-1" max-height="90px" contain></v-img>
    </div>
  </v-card>
</template>

<script>
import { mapGetters } from 'vuex'
import { get as getSafe } from 'lodash'
import truckHelpers from '@/helpers/truckHelper.js'

export default {
  props: {
    fields: { default: () => [] },
  },

  data() {
    return {
      page: 1,
    }
  },

  computed: {
    ...mapGetters({
      getItemsWithKey: 'dynamic/getItemsWithKey',
      loading: 'dynamic/loading',
    }),
    url() {
      return process.env.baseURL
    },
  },

  methods: {
    getSafe,
    ...truckHelpers,
    paginate(number) {
      this._event('autoRefresh', false)

      if (number > 0) this.page++
      else this.page--

      this.$store.dispatch('dynamic/get', {
        page: this.page,
        key: 'OcrLog',
      })
    },
  },
}
</script>
