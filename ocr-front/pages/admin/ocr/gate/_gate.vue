<template>
  <div class="d-flex flex-column">
    <div class="d-flex flex-row flex-wrap align-center justify-center">
      <CameraWidget v-for="gate in gates" :key="gate" :gate="this.matchGate" />
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
      1: '1',
      2: '2',
      3: '3',
      4: '4',
    },
  }),

  computed: {

    sseUrl() {
      alert(this.$route.params.gate)
      return 'api/sse/ocr-log?receiver_id=' + this.$route.params.gate
    },
    gateId() {
      return this.$route.params.gate
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
