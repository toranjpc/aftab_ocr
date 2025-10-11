<template>
  <span
    style="
      background-image: url(/container.jpg);
      background-size: contain;
      padding-left: 15px;
      font-weight: bold;
      max-width: fit-content;
      font-size: 16px;
      padding-top: 9px;
      padding-bottom: 8px;
      padding-right: 30px;
      background-position: center;
      background-position-x: center;
      background-position-y: center;
      position: relative;
      min-width: 300px;
      min-height: 300px;
    "
    class="d-flex flex-row-reverse black align-center rounded-lg elevation-5 overflow-hidden"
  >
    <div class="text-container" style="font-size: 1rem">
      <div class="d-flex">
        <span style="border: 1px solid white; margin-left: 7px">
          {{ getContainerCode('[3]') }}
        </span>
        <span style="margin-left: 17px">
          {{ getContainerCode('[2][0]') }}
        </span>
        <span>
          {{ getContainerCode('[2][1]') }}
        </span>
      </div>
      <div class="ml-4">
        {{ getContainerCode('[4]') }}
      </div>
    </div>
  </span>
</template>

<script>
import { get as getSafe } from 'lodash'

export default {
  props: ['selectedTruck'],

  methods: {
    getContainerCode(part, match = false) {
      const containerCode = getSafe(this.selectedTruck, 'container_code', '')

      if (typeof containerCode !== 'string') return

      const split = containerCode.split(',')

      split[4] = split[4] === 'None' ? '' : split[4]

      if (typeof split[2] !== 'string') return

      split[2] = [split[2].replace(/\D/g, ''), split[2].replace(/\d/g, '')]

      const slectedpart = getSafe(split, part, '')

      return slectedpart
    },
  },
}
</script>

<style>
.text-container {
  position: absolute;
  color: rgb(255, 255, 255);
  left: 58%;
  top: 7%;
  padding: 0.5rem;
  text-align: center;
  font-size: 16px;
}
.gfg {
  position: relative;
  width: 100%;
  text-align: center;
}
</style>
