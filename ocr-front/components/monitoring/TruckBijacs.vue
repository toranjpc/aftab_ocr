<template>
  <CardTitle
    v-if="bijacs && bijacs?.length"
    title="اطلاعات بیجک"
    class="fill-height"
  >
    <div class="d-flex justify-space-between">
      <v-btn
        icon
        :disabled="selectedIndex === bijacs?.length - 1 || bijacs?.length === 0"
        @click="selectedIndex++"
      >
        <v-icon>fal fa-arrow-right</v-icon>
      </v-btn>
      <v-btn icon :disabled="selectedIndex === 0" @click="selectedIndex--">
        <v-icon>fal fa-arrow-left</v-icon>
      </v-btn>
    </div>

    <table style="width: 100%" class="pa-1">
      <tr>
        <td v-for="(field, index) in bijacFields">
          {{ field.title }}
        </td>
      </tr>
      <tr>
        <td v-for="(field, index) in bijacFields" :key="field.field">
          {{
            'convert' in field
              ? field.convert(getSafe(bijacs[selectedIndex], field.field))
              : getSafe(bijacs[selectedIndex], field.field)
          }}
        </td>
      </tr>
    </table>
  </CardTitle>
</template>

<script>
import { get as getSafe } from 'lodash'
import CardTitle from '~/components/widgets/CardTitle.vue'

export default {
  components: { CardTitle },

  props: {
    truck: {},
    bijacFields: {},
  },

  data: () => ({
    selectedIndex: 0,
  }),

  computed: {
    bijacs() {
      if (getSafe(this.truck, 'parent.bijacs', []).length > 0) {
        return getSafe(this.truck, 'parent.bijacs', [])
      }

      return getSafe(this.truck, 'bijacs')
    },
  },

  methods: { getSafe },
}
</script>
