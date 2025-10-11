<template>
  <CardTitle title="تصاویر" class="fill-height">
    <div class="pa-3 d-flex flex-row align-center">
      <div class="col-3" style="position: relative">
        <img style="border-radius: 10px; margin-top: 5px; width: 100%"
          :src="url + getSafe(truck, 'vehicle_image_front_url')" class="mx-1" />
        <img style="
            border-radius: 10px;
            margin-top: 5px;
            max-width: 40%;
            position: absolute;
            left: -5px;
            bottom: 5px;
            border: 2px solid white;
          " :src="url + getSafe(truck, 'plate_image_url')" />
      </div>

      <!-- <div class="col-3">
        <img v-if="getSafe(truck, 'vehicle_image_back_url')" style="border-radius: 10px; margin-top: 5px; width: 100%"
          :src="url + getSafe(truck, 'vehicle_image_back_url')" class="mx-1" />
      </div> -->

      <div class="col-3" style="position: relative">
        <img v-if="getSafe(truck, 'vehicle_image_back_url')"
          style="border-radius: 10px; margin-top: 5px; width: 100%"
          :src="url + getSafe(truck, 'vehicle_image_back_url')" />
        <img v-if="getSafe(truck, 'container_code_image_url')" style="
            border-radius: 10px;
            margin-top: 5px;
            max-width: 40%;
            position: absolute;
            left: -5px;
            bottom: 5px;
            border: 2px solid white;
          " class="mx-1" :src="url + getSafe(truck, 'container_code_image_url')" />
      </div>

      <div v-html="ConvertToImg(
        getSafe(truck, 'plate_number', '--ein-----'),
        getSafe(truck, 'plate_type', 'iran'),
        false
      )
        "></div>
    </div>
  </CardTitle>
</template>

<script>
import { get as getSafe } from 'lodash'
import CardTitle from '~/components/widgets/CardTitle.vue'
import ConvertToImg from '@/helpers/NormalizeVehicleNumberAsImg.js'

export default {
  components: { CardTitle },

  props: {
    truck: {},
  },

  computed: {
    url() {
      return process.env.baseURL
    },
  },

  methods: { getSafe, ConvertToImg },
}
</script>
