<template>
  <div class="d-flex flex-column">
    <div class="d-flex flex-row flex-wrap align-center justify-center">
      <CameraWidget v-for="gate in gates" :key="gate" :gate="mapGates[gate]" />
    </div>

    <DynamicTemplate>
      <template #header-btn>
        <SseBtn :route="sseUrl" />
      </template>

      <template #actions="item">
        <div v-if="item.bijacs.length > 0" class="ml-2">
          <v-btn small color="success" dark @click="_event('ccs.dialog', item)">
            نمایش فاکتور
          </v-btn>
        </div>
      </template>

      <template #extra>
        <FactorDialog />
      </template>
    </DynamicTemplate>
  </div>
</template>

<script>
import fields from './../fields'
import { get as getSafe } from 'lodash'
import { DynamicTemplate } from 'majra'
import SseBtn from '@/components/widgets/SseBtn'
import { getPermissions } from '~/helpers/helpers'
import CameraWidget from '~/components/widgets/CameraWidget.vue'
import FactorDialog from '~/components/truckLog/FactorDialog.vue'

export default {
  components: { DynamicTemplate, SseBtn, CameraWidget, FactorDialog },

  layout: 'dashboard',

  data: () => ({
    dialog: false,
    sse: false,
    map: {
      east: [2, 3, 4],
      west: [1],
    },
    mapGates: {
      1: 'west_1',
      2: 'east_1',
      3: 'east_2',
      4: 'east_3',
    },
  }),

  computed: {
    sseUrl() {
      return 'api/sse/ocr-log?receiver_id=' + this.gates.join(',')
    },

    gates() {
      const gate = this.$route.params.gate

      const keys = Object.keys(this.map)

      if (keys.includes(gate)) return this.map[gate]

      return [gate]
    },
  },

  created() {
    const hiddenActions = getPermissions.call(this)

    this.$majra.init({
      hiddenActions,
      mainRoute: {
        route:
          '/ocr-match?_append=invoice&_with=bijacs&filters[gate_number][$eq]=' +
          this.gates.join(','),
        key: 'OcrMatch',
      },
      relations: [],
      fields: fields(this),
    })
  },

  methods: {
    getSafe,
  },
}
</script>
