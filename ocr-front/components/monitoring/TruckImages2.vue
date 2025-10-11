<template>
  <div class=" d-flex flex-column align-center">
    <div class="col-12 pb-0" style="position: relative; max-height: fit-content;">
      <img style="border-radius: 4px; margin-top: 5px; width: 100%"
        :src="url + getSafe(truck, 'vehicle_image_front_url')" class="mx-1" />
      <img style="
            border-radius: 3px;
            margin-top: 5px;
            max-width: 40%;
            position: absolute;
            left: 14px;
            bottom: 29px;
            border: 1px solid white;
          " :src="url + getSafe(truck, 'plate_image_url')" />
    </div>
    <div class="d-flex">
      <div class="col-6 py-0">
        <img v-if="getSafe(truck, 'vehicle_image_back_url')" style="border-radius: 4px; margin-top: 5px; width: 100%"
          :src="url + getSafe(truck, 'vehicle_image_back_url')" class="mx-1" />
      </div>

      <div class="col-6 py-0" style="position: relative">
        <img v-if="getSafe(truck, 'parent.vehicle_image_front_url')"
          style="border-radius: 4px; margin-top: 5px; width: 100%"
          :src="url + getSafe(truck, 'parent.vehicle_image_front_url')" />
        <img v-if="getSafe(truck, 'parent.container_code_image_url')" style="
            border-radius: 3px;
            margin-top: 5px;
            max-width: 40%;
            position: absolute;
            left: 14px;
            bottom: 29px;
            border: 1px solid white;
          " class="mx-1" :src="url + getSafe(truck, 'parent.container_code_image_url')" />
      </div>
    </div>
  </div>
</template>

<script>
import { get as getSafe } from 'lodash'
import CardTitle from '~/components/widgets/CardTitle.vue'

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

  methods: { getSafe },
}
</script>
