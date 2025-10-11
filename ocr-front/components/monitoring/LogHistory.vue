<template>
  <div>
    <v-btn small class="mx-2" icon @click="dialog = true">
      <v-icon>fal fa-history</v-icon>
    </v-btn>

    <v-dialog v-model="dialog" max-width="800">
      <v-card id="dialog">
        <v-card-title>
          <span>تاریخچه تغییرات</span>
          <v-spacer />
          <v-btn color="error" icon @click="dialog = false">
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="mt-4">
          <CardWidget
            v-for="log in logs"
            class="mb-1"
            :title="
              persianDateGlobal(log.log_date, 'dateTime') +
              ' (' +
              getSafe(log, 'user.name', 'سیستم') +
              ') '
            "
            :key="log.id"
          >
            <div class="d-flex flex-row">
              <div
                v-if="log.data.plate_number"
                class="pa-2"
                v-html="
                  NormalizeVehicleNumberAsImg(
                    log.data.plate_number,
                    log.data.plate_type
                  )
                "
              />
              <div
                v-if="log.data.container_code"
                class="pa-2"
                v-html="NormalizeContainerCodeAsImg(log.data.container_code)"
              />
            </div>
          </CardWidget>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { get as getSafe } from 'lodash'
import { persianDateGlobal } from '@/helpers/helpers'
import CardWidget from '@/components/widgets/CardWidget.vue'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default {
  components: { CardWidget },

  props: {
    ocrLog: {},
  },

  data() {
    return {
      dialog: false,
      logs: [],
    }
  },

  watch: {
    dialog() {
      if (this.dialog) {
        this.getLogs()
      }
    },
  },

  methods: {
    getSafe,
    persianDateGlobal,
    NormalizeContainerCodeAsImg,
    NormalizeVehicleNumberAsImg,
    getLogs() {
      this._event('loading', true)
      this.$axios
        .$post('/get-ocr-logs', { id: this.ocrLog.id })
        .then((res) => {
          this.logs = res.logs
        })
        .finally(() => {
          this._event('loading', false)
        })
    },
  },
}
</script>
