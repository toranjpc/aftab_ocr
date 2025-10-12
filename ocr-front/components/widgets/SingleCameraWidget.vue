<template>
  <div class="mb-3 mx-1" style="max-width: 100%">
    <div v-show="label" class="col-12 info white--text pt-0 rounded-lg">
      <v-icon dark right>fal fa-cctv</v-icon>
      <b>دوربین شماره {{ camera }}</b>
    </div>

    <v-card style="position: relative">
      <canvas v-if="cameras[0]" :id="`canvas-${cameras[0].id}`" style="width: 100%; height: auto;"></canvas>

      <div v-else class="pa-3 text-center rounded-lg" style="
          min-height: 186px;
          background-image: url(/img/x.jpg);
          color: #ffffff;
          display: flex;
          justify-content: center;
          align-items: center;
          font-weight: bold;
        ">
        <div class="fa-3x">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <!-- دوربین بدون تصویر -->
      </div>
      <!-- <v-img v-else :src="'data:image/png;base64, ' + image.frame" /> -->

    </v-card>
  </div>
</template>

<script>
require('@/plugins/jsmpeg.min.js')
import { get as getSafe } from 'lodash'
import { loadPlayer } from 'rtsp-relay/browser';

let socket

export default {
  props: {
    matchGate: { required: true, default: 1 },
    gate: { default: 1 },
    camera: { default: 1 },
    label: { default: true },
    plate: { default: false },
  },

  data() {
    return {
      image: '',
      cameras: {},
      players: {},

      cameraDet: process.env.cameraUrls[this.matchGate] || [],
    }
  },

  // mounted() {
  //   this.connectToCamera(this.camera)
  // },

  beforeDestroy() {
    Object.values(this.players).forEach((player, index) => {
      if (typeof player.stop === 'function') {
        player.stop();
        delete this.players[index];
      }
    });
  },

  created() {
    this.getCameras()
  },

  methods: {
    getSafe,
    connectToCamera(cameraId) {
      socket = new WebSocket(
        process.env.websocketDomain + '?camera_id=' + cameraId
      )
      socket.addEventListener('message', (data) => {
        const frame = JSON.parse(data.data)

        this.image = frame
      })
    },

    getCameras(group) {
      let q = ''

      if (this.plate) {
        q += '&filters[type][$eq]=plate'
      }

      this.$axios
        .get('/camera?filters[group][$eq]=' + this.gate + '&filters[active][$eq]=1' + q)
        .then((res) => {
          this.cameras = getSafe(res, 'data.Camera.data', [])
          return this.cameras;
        }).then((cameras) => {
          cameras.forEach(camera => {
            this.loadStream(camera);
          });
        })
    },

    loadStream(camera) {
      if (this.players[camera.id]) return;
      this.players[camera.id] = loadPlayer({
        // url: `ws://localhost:2000/api/stream/${camera.type}`,
        // url: `ws://${window.location.host}/ws/api/stream/${camera.type}`,
        // url: `ws://${this.cameraDet.camera.ip}/ws/api/stream/${camera.type}`,
        // url: `ws://${camera.ip}/ws/api/stream/${camera.type}`,
        url: `ws://${window.location.hostname}:4200/cam/${this.gate}`,
        canvas: document.getElementById(`canvas-${camera.id}`)
      });

      console.log(this.cameraDet)

    }
  },
}
</script>
