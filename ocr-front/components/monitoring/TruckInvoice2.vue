<template>
  <div class="mb-6 d-flex justify-space-between">
    <div
      v-if="invoiceFields && invoiceFields.length && invoiceFields.some(field => getSafe(truck, 'invoice.' + field.field))"
      class="d-flex justify-space-between" style="min-width: 100%;">
      <span v-for="(field, index) in invoiceFields" :key="field.field"
        v-if="field.field && getSafe(truck, 'invoice.' + field.field)">
        <span class="mx-2 font-weight-bold">
          {{ field.title ? field.title : 'بدون مقدار' }}
        </span>
        <span>
          {{
            'convert' in field
              ? field.convert(getSafe(truck, 'invoice.' + field.field))
              : getSafe(truck, 'invoice.' + field.field)
          }}
        </span>
      </span>
    </div>
    <div v-else style="width: 100%; border-radius: 3px;"
      class="blueLogo pa-1 text-center text--yellowLogo  yellowLogo--text mb-5 font-weight-bold" dark>
      فاکتوری
      پیدا
      نشد</div>
  </div>
</template>

<script>
import { get as getSafe } from 'lodash'

export default {
  props: {
    truck: {
      type: Object,
      default: () => ({}),
    },
    invoiceFields: {
      type: Array,
      default: () => [],
    },
  },
  methods: { getSafe },
}
</script>
