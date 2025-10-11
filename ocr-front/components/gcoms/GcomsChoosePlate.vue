<template>
  <v-dialog v-model="dialog" width="600">
    <v-card>
      <v-card-title>
        <span>افزودن</span>
        <v-spacer />
        <v-btn color="error" icon @click="dialog = false">
          <v-icon>fal fa-times</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="justify-center align-center d-flex">
        <span class="ma-3 pa-3 mx-auto">
          <v-btn
            v-for="(plateButton, index) in plateButtons"
            :key="index"
            style="width: 70px; height: 70px"
            dark
            :color="plateButton.color"
            class="mx-2"
            @click="selectedComponent = plateButton.component"
          >
            {{ plateButton.plateType }}
          </v-btn>
        </span>
      </v-card-text>

      <v-card-text>
        <component :is="selectedComponent" @update="plate = $event" />
      </v-card-text>

      <v-card-actions>
        <v-btn color="success px-4" @click="save">ثبت پلاک</v-btn>
        <v-btn color="error px-4" @click="dialog = false">لغو</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import IranPlate from '@/components/utilities/IranPlate.vue'
import OtherPlate from '@/components/utilities/OtherPlate.vue'
import AfghanPlate from '@/components/utilities/AfghanPlate.vue'

export default {
  components: { AfghanPlate, IranPlate, OtherPlate },

  data() {
    return {
      plate: '',
      dialog: false,
      selectedComponent: 'IranPlate',
      plateButtons: [
        { plateType: 'ایران', component: 'IranPlate', color: 'green' },
        { plateType: 'افغان', component: 'AfghanPlate', color: 'black' },
        { plateType: 'متفرقه', component: 'OtherPlate', color: 'warning' },
      ],
    }
  },

  created() {
    this._listen('showDialogChoosPlate', () => {
      this.dialog = true
    })
  },

  methods: {
    save() {
      this.$emit('update', this.plate)
      this.dialog = false
    },
  },
}
</script>
