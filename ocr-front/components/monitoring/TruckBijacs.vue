<template>
  <CardTitle v-if="bijacs && bijacs?.length" title="اطلاعات بیجک" class="fill-height">
    <div class="d-flex justify-space-between">
      <v-btn icon :disabled="selectedIndex === bijacs?.length - 1 || bijacs?.length === 0" @click="selectedIndex++">
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
        <td v-for="(field, index) in bijacFields" :key="field.field"
          :class="field.field === 'plate_normal' ? plateStyle : {}">
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

    plateStyle() {
      if (!this.truck || !this.bijacs || this.bijacs.length === 0 || this.selectedIndex === undefined) {
        return { backgroundColor: 'transparent' };
      }

      const currentTruckPlate = getSafe(this.truck, 'plate_number', '');
      const bijacData = this.bijacs[this.selectedIndex];
      const bijacPlate = getSafe(bijacData, 'plate_normal', '');

      const isMatch = this.isPlateSimilar(currentTruckPlate, bijacPlate);

      if (!isMatch) {
        return "blink-bg"
        return { backgroundColor: '#ffcccc' };
      }
      return ""
      return { backgroundColor: 'transparent' };
    },
    // SELECT `plate_normal`,`receipt_number`,`container_number`,`bijac_date`,`bijac_number` FROM `bijacs` WHERE `receipt_number` LIKE 'BSRGCBI10412757' AND `bijac_number` LIKE '3233587' ORDER BY `bijac_number` DESC;


  },

  methods: {
    removeNonDigits(str) {
      if (typeof str !== 'string') return '';
      return str.replace(/\D/g, '');
    },

    isPlateSimilar(inputStr, plateNumberStr) {
      if (!inputStr || !plateNumberStr) {
        return false;
      }

      if (
        inputStr.includes(plateNumberStr) ||
        plateNumberStr.includes(inputStr)
      ) {
        return true;
      }

      const inputSubstr = this.removeNonDigits(inputStr);
      const plateNumberSubstr = this.removeNonDigits(plateNumberStr);

      if (
        inputSubstr.includes(plateNumberSubstr) ||
        plateNumberSubstr.includes(inputSubstr)
      ) {
        return true;
      }

      return false;
    },

    getSafe
  },

}
</script>

<style>
.blink-bg {
  padding: 4px 10px;
  background-color: #f27474;
  display: inline-block;
  border-radius: 5px;
  animation: blinkBackground 1s infinite;
}

@keyframes blinkBackground {
  0% {
    background-color: #f27474;
  }

  50% {
    background-color: transparent;
  }

  100% {
    background-color: #f27474;
  }
}
</style>