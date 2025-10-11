<template>
  <FieldSet label="پلاک">
    <div class="justify-center align-center d-flex">
      <span class="ma-3 pa-3 mx-auto">
        <v-btn v-for="(plateButton, index) in plateButtons" :key="index" style="width: 70px; height: 70px"
          :color="plateButton.color" class="mx-2" dark @click="selectedComponent = plateButton.component">
          {{ plateButton.plateType }}
        </v-btn>
      </span>

      <!-- <v-btn
        color="danger"
        class="mx-1"
        icon
        small
        @click=";[updateField(''), (selectedComponent = '')]"
      >
        <v-icon small>fal fa-times</v-icon>
      </v-btn> -->
    </div>

    <component :is="selectedComponent" :plate-attributes="{ normalPlate: value }" @update="updateField($event)" />
  </FieldSet>
</template>

<script>
import { AbstractField, FieldSet } from 'majra'
import IranPlate from '@/components/utilities/IranPlate.vue'
import OtherPlate from '@/components/utilities/OtherPlate.vue'
import AfghanPlate from '@/components/utilities/AfghanPlate.vue'

export default {
  extends: AbstractField,

  components: {
    FieldSet,
    IranPlate,
    OtherPlate,
    AfghanPlate,
  },

  data() {
    return {
      selectedComponent: 'div',
      plateButtons: [
        { plateType: 'ایران', component: 'IranPlate', color: 'green' },
        { plateType: 'افغان', component: 'AfghanPlate', color: 'black' },
        { plateType: 'متفرقه', component: 'OtherPlate', color: 'warning' },
      ],
    }
  },
}
</script>
