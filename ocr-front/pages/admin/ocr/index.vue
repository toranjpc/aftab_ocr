<template>
  <div class="d-flex flex-column">
    <CameraWidget :col="3" gate="west_1" />

    <DynamicTemplate>
      <template #header-btn>
        <SseBtn :route="`api/sse/ocr-log?receiver_id=${gateId}&gate_number=${gateId}`" />
      </template>

      <template #item.plate_number="item">
        <!-- <EditBtn :editItem="item" :fields="plateFields(item)" /> -->
        <div v-html="plateShow(item.plate_number, item)"></div>
      </template>

      <template #item.container_code="item">
        <!-- <EditBtn :editItem="item" :fields="containerFields(item)" /> -->
        <div v-html="containerCodeShow(item.container_code, item)"></div>
      </template>
    </DynamicTemplate>

    <template>
      <div id="full-screen-overlay"></div>
      <img id="full-screen-image" src="" alt="Full Screen Image" />
    </template>
  </div>
</template>

<script>
import fields from './fields'
import { get as getSafe } from 'lodash'
import { DynamicTemplate } from 'majra'
import SseBtn from '@/components/widgets/SseBtn'
import { getPermissions } from '@/helpers/helpers'
import EditBtn from '@/components/utilities/EditBtn'
import PlateField from '@/components/utilities/PlateField'
import CameraWidget from '@/components/widgets/CameraWidget.vue'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'

export default {
  components: { DynamicTemplate, CameraWidget, SseBtn, EditBtn },

  layout: 'dashboard',

  data: () => ({
    dialog: false,
    gateId: 1,
  }),

  created() {
    const hiddenActions = getPermissions.call(this)

    this.$majra.init({
      hiddenActions,
      mainRoute: { route: '/ocr-log', key: 'OcrLog' },
      relations: [],
      fields: fields(this),
    })
  },

  mounted() {
    function showFullScreenImage(imageUrl) {
      var fullScreenImage = document.getElementById('full-screen-image')
      var fullScreenOverlay = document.getElementById('full-screen-overlay')

      fullScreenImage.src = imageUrl
      fullScreenImage.style.display = 'block'
      fullScreenOverlay.style.display = 'block'

      fullScreenImage.addEventListener('click', function () {
        fullScreenImage.style.display = 'none'
        fullScreenOverlay.style.display = 'none'
      })

      fullScreenOverlay.addEventListener('click', function () {
        fullScreenImage.style.display = 'none'
        fullScreenOverlay.style.display = 'none'
      })
    }

    function getEls() {
      const els = document.getElementsByClassName('resizable')
      if (els.length === 0) {
        return setTimeout(() => {
          getEls()
        }, 1000)
      }

      for (const el of els) {
        el.addEventListener('click', () => {
          showFullScreenImage(el.src)
        })
      }
    }

    if (this.$vuetify.breakpoint.mobile) {
      getEls()
    }
  },

  methods: {
    getSafe,
    plateFields: (item) => [
      {
        title: 'شماره پلاک',
        field: 'plate_number_edit',
        component: PlateField,
        normalize() {
          return item.plate_number
        },
      },
      {
        title: 'id',
        field: 'id',
        type: 'hidden',
      },
    ],
    containerFields: (item) => [
      {
        title: 'کد کانتینر',
        field: 'container_code_edit',
        type: 'text',
        normalize() {
          return item.container_code
        },
      },
      {
        title: 'id',
        field: 'id',
        type: 'hidden',
      },
    ],
    containerCodeShow(v, form) {
      let concat = ''

      if (form.container_code_2 && form.container_code_2 != v)
        concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2)

      if (v) {
        return (
          NormalizeContainerCodeAsImg(
            form.container_code_edit || v,
            form.container_code_edit ? 'green' : '#2957a4'
          ) + concat
        )
      }

      return '-'
    },
    plateShow(v, form) {
      let concat = ''

      if (form.plate_number_2 && form.plate_number_2 != v)
        concat =
          '</br>' +
          NormalizeVehicleNumberAsImg(
            form.plate_number_2 || '',
            form.plate_type
          )

      return (
        NormalizeVehicleNumberAsImg(
          form.plate_number_edit || v || '',
          form.plate_type,
          !!form.plate_number_edit
        ) + concat
      )
    },
  },
}
</script>

<style>
#full-screen-image {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  max-width: 100%;
  max-height: 100%;
  z-index: 9999;
  display: none;
}

#full-screen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  z-index: 9998;
  display: none;
}
</style>
