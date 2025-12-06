<template>
  <v-card class="elevation-0 transparent">
    <div v-if="loading['OcrMatch']" style="
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

    <div class="col-12 pa-0 px-2 d-flex flex-row justify-space-between rounded-lg"
      style="border: 1px solid rgba(0, 0, 0, 0.3)">
      <v-btn color="transparent" elevation="0" small :disabled="page > 10" @click="paginate(1)">
        <v-icon left>fal fa-arrow-right</v-icon>
        <span>بعدی</span>
      </v-btn>

      <div class="d-flex flex-row align-center justify-center">
        <AddPlateDialog :matchGate="matchGate" :page.sync="page" />

        <!-- <v-btn color="transparent" elevation="0" small @click="_event('showDialogChoosPlate')">
          افزودن پلاک دستی
        </v-btn> -->
      </div>

      <v-btn color="transparent" elevation="0" small :disabled="page === 1" @click="paginate(-1)">
        <span>قبلی</span>
        <v-icon right>fal fa-arrow-left</v-icon>
      </v-btn>
    </div>

    <div class="d-flex flex-row flex-wrap">
      <div v-for="truck in getItemsWithKey('OcrMatch')" :key="truck.id"
        class="cursor-pointer my-1 rounded-lg col-6 pa-1" style="position: relative" @click="$emit('select', truck)">
        <img :style="'border: 2px solid ' + statusColor(truck)"
          style="border-radius: 10px; margin-top: 5px; max-width: 100%"
          :src="url + (truck.vehicle_image_front_url ?? truck.vehicle_image_back_url)" />
        <img style="
            border-radius: 10px;
            margin-top: 5px;
            max-width: 30%;
            position: absolute;
            left: 0px;
            bottom: 5px;
            border: 2px solid white;
          " :src="url + (truck.plate_image_url ?? truck.container_code_image_url)" />
      </div>
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
    matchGate: { type: [String, Number], default: '' },
    page: {
      type: Number,
      required: true,
    },
  },

  data() {
    return {
      // page: 1,
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

      const newPage = this.page + (number > 0 ? 1 : -1)
      this.$emit('update:page', newPage)

      this.$store.dispatch('dynamic/get', {
        page: newPage,
        key: 'OcrMatch',
      })
    },
  },
}
</script>
