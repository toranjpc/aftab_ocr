<template>
  <CardTitle v-if="getSafe(truck, 'invoice')" title="اطلاعات فاکتور">
    <table style="width: 100%" class="pa-1">
      <tr>
        <td v-for="(field, index) in invoiceFields">
          {{ field.title }}
        </td>
      </tr>
      <tr>
        <td v-for="(field, index) in invoiceFields" :key="field.field">
          {{
            'convert' in field
              ? field.convert(getSafe(truck, 'invoice.' + field.field))
              : getSafe(truck, 'invoice.' + field.field)
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
    invoiceFields: {},
  },

  methods: { getSafe },
}
</script>
