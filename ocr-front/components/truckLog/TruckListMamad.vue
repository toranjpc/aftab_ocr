<template>
  <v-card>
    <div
      v-if="loading['TruckLog']"
      style="
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
        background-color: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
      "
      class="d-flex align-center justify-center"
    >
      <i class="fas fa-spinner fa-spin fa-2x"></i>
    </div>

    <v-card-text>
      <table class="col-12 pa-0 text-center">
        <thead>
          <tr>
            <td
              v-for="field in fields"
              :key="field.field"
              class="pa-1 rounded-lg"
              :class="{ primary: field.field === 'status' }"
            >
              <b>{{ field.title }}</b>
            </td>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="truck in getItemsWithKey('TruckLog')"
            :key="truck.id"
            class="cursor-pointer"
            @click="$emit('select', truck)"
          >
            <td
              v-for="field in fields"
              :key="field.field"
              class="rounded-lg pa-0"
              :style="'border: 1px solid ' + statusColor(truck)"
            >
              <template v-if="!field.inList">
                {{ getSafe(truck, field.field, '------') }}
              </template>
              <template v-else>
                <div
                  class="d-flex justify-center"
                  v-html="
                    field.inList(getSafe(truck, field.field, '------'), truck)
                  "
                ></div>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </v-card-text>
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

  computed: {
    ...mapGetters({
      getItemsWithKey: 'dynamic/getItemsWithKey',
      loading: 'dynamic/loading',
    }),
  },

  methods: { getSafe, ...truckHelpers },
}
</script>
