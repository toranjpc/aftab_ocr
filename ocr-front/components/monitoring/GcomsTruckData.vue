<template>
  <div>
    <GcomsChoosePlate @update="addPlate" />

    <GcomsTruckReportDialog :activePlateNumber="truck" />
  </div>
</template>

<script>
import GcomsChoosePlate from '~/components/gcoms/GcomsChoosePlate'

export default {
  props: {
    truck: {},
  },

  components: { GcomsChoosePlate },

  methods: {
    addPlate(plate) {
      this._event('loading')
      this.$axios
        .$post('/ocr-log', {
          plate_number: plate.normalPlate,
          type: plate.type,
        })
        .then((res) => {
          console.log(res)
          this._event('selected.truck.change', res.OcrLog)
        })
        .finally(() => {
          this._event('loading', false)
        })

      if (plate.isValid) this.focusOn('receipt_number')
    },

    setActivePlate(plate) {
      const { normalPlate, type } = plate

      this.activePlateNumber = {
        plate_number: normalPlate,
        plate_type: type,
      }
    },

    focusOn(field) {
      document.getElementById(field)?.focus()
      setTimeout(() => {
        document.getElementById(field)?.focus()
      }, 200)
    },
  },
}
</script>
