<template>
  <span
    style="
      background-image: url(/img/afghan.png);
      background-size: contain;
      font-weight: bold;
      max-width: fit-content;
      font-size: 16px;
      padding-top: 12px;
      padding-bottom: 12px;
      background-position: center;
      background-position-x: center;
      background-position-y: center;
      position: relative;
      justify-content: flex-end;
      min-width: 120px;
    "
    class="d-flex flex-row-reverse align-center"
  >
    <span
      style="
        left: 8px;
        position: absolute;
        justify-content: space-between;
        margin-top: 2px;
        padding-right: 2px;
        font-size: 10px;
        display: flex;
        flex-direction: column;
        height: 66%;
        text-align: center;
      "
    >
      <span>{{ convertToPersian(plate.city) }}</span>
      <span>{{ plate.city }}</span>
    </span>

    <span
      style="
        display: flex;
        flex-direction: column;
        width: 100%;
        justify-content: center;
        align-items: center;
        margin-left: 13px;
      "
    >
      <span> {{ plate.number || '---' }} </span>
      <span style="font-family: sans-serif">{{ plate.number || '---' }} </span>
    </span>

    <span
      style="
        right: 10px;
        position: absolute;
        justify-content: space-between;
        margin-top: 2px;
        padding-right: 2px;
        font-size: 12px;
        display: flex;
        flex-direction: column;
        height: 57%;
        text-align: center;
      "
    >
      <span>ل</span>
      <span>L</span>
    </span>
  </span>
</template>

<script>
import { afghanCities } from '~/helpers/plateInfo'
import { get as getSafe } from 'lodash'

export default {
  props: { plate: {} },

  methods: {
    convertToPersian() {
      const find = afghanCities.find((item) => item.value === this.plate.city)
      return getSafe(find, 'text', '----')
    },
  },
}
</script>
