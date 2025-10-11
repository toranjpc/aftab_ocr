<template>
  <v-card class="ma-1 mx-1">
    <v-img
      height="120"
      class="d-none d-lg-flex"
      :src="url + item.vehicle_image_front_url"
    />

    <v-card-title class="pb-0 d-none d-md-flex">
      <span
        v-html="NormalizeVehicleNumberAsImg(item.plate_number, item.plate_type)"
      ></span>
    </v-card-title>

    <v-card-text class="d-none d-lg-flex" style="position: absolute">
      <span style="position: absolute; left: 15px; top: -80px">
        <v-img
          style="border-radius: 10px;box-shadow: 0px 0px 5px black;"
          width="100px"
          :src="url + item.plate_image_url"
        />
      </span>
    </v-card-text>

    <v-card-text class="pa-0 d-flex d-lg-none">
      <v-img
        style="border-radius: 10px"
        width="100px"
        :src="url + item.plate_image_url"
      />
    </v-card-text>

    <v-card-actions class="pa-1 pa-md-2">
      <v-spacer />
      <v-btn text @click="reserve(item)">
        <span
          class="deep-purple--text lighten-2"
          v-show="item.plate_number !== activePlateNumber?.plate_number"
        >
          انتخاب
        </span>
        <span
          class="red--text lighten-2"
          v-show="item.plate_number === activePlateNumber?.plate_number"
        >
          حذف
        </span>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default {
  props: {
    item: { default: () => ({}) },
    activePlateNumber: {},
  },

  computed: {
    url() {
      return process.env.baseURL
    },
  },

  methods: {
    NormalizeVehicleNumberAsImg,

    reserve(v) {
      if (this.activePlateNumber?.plate_number === v.plate_number)
        return this.$emit('updateActivePlate', null)

      this.$emit('updateActivePlate', v)
    },
  },
}
</script>
