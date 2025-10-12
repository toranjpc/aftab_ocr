<template>
  <div class="w-100 d-flex flex-row flex-wrap justify-center">
    <div
      v-for="(camera, key) in cameras"
      :key="'_' + key"
      class="col-12 mb-3 mx-1"
      :class="'col-md-' + col"
      style="max-width: 350px"
    >
      <div v-show="label" class="col-12 info white--text mb-2 rounded-lg">
        <v-icon dark right>fal fa-cctv</v-icon>
        <b>{{ camera.name }}</b>
      </div>

      <v-card style="position: relative">
        <canvas :id="`canvas-${camera.id}`" style="width: 100%; height: auto"></canvas>
      </v-card>
    </div>
  </div>
</template>

<script>
require("@/plugins/jsmpeg.min.js");
import { get as getSafe } from "lodash";
import { loadPlayer } from "rtsp-relay/browser";

export default {
  props: {
    matchGate: { required: true, default: 1 },
    gate: { default: 1 },
    col: { default: 12 },
    label: { default: true },
    plate: { default: false },
  },

  data() {
    return {
      // isScriptLoaded: false,
      cameras: {},
      players: {},

      cameraDet: process.env.cameraUrls[this.matchGate] || [],
    };
  },

  // mounted() {
  //   this.loadScriptOnce();
  // },

  beforeDestroy() {
    Object.values(this.players).forEach((player, index) => {
      if (typeof player.stop === "function") {
        player.stop();
        delete this.players[index];
      }
      // player.destroy(); // بستن اتصالات WebSocket
    });
    // const scripts = document.querySelectorAll('script[src*="rtsp-relay"]');
    // scripts.forEach(
    //   script => document.head.contains(script) ??
    //     document.head.removeChild(script)
    // );
  },

  created() {
    this.getCameras();
  },
  mounted() {},
  methods: {
    getSafe,

    getCameras(group) {
      let q = "";

      if (this.plate) {
        q += "&filters[type][$eq]=plate";
      }

      this.$axios
        .get("/camera?filters[group][$eq]=" + this.gate + "&filters[active][$eq]=1" + q)
        .then((res) => {
          // console.log(res)

          this.cameras = getSafe(res, "data.Camera.data", []);
          return this.cameras;
        })
        .then((cameras) => {
          cameras.forEach((camera) => {
            this.loadStream(camera);
          });
        });
    },

    // loadScriptOnce() {
    //   return new Promise((resolve) => {
    //     if (this.isScriptLoaded) return resolve();

    //     const script = document.createElement('script');
    //     script.src = "https://cdn.jsdelivr.net/npm/rtsp-relay@1.9.0/browser/index.js";
    //     script.onload = () => {
    //       this.isScriptLoaded = true;
    //       resolve();
    //     };
    //     document.head.appendChild(script);
    //   });
    // },

    loadStream(camera) {
      if (this.players[camera.id]) return;
      // await this.loadScriptOnce();
      this.players[camera.id] = loadPlayer({
        // videoBufferSize: 10 * 1024 * 1024,
        // url: `ws://46.148.36.110:8000/ws/api/stream/${camera.type}`,
        // url: `ws://${window.location.host}/ws/api/stream/${camera.type}`,
        // url: `ws://${window.location.host}/ocr/ws/api/stream2/${camera.type}`,
        url: `ws://${window.location.hostname}:4200/cam/${this.gate}`,
        // url: `ws://${window.location.hostname}/cam/${camera.type}`,

        // url: `ws://${this.cameraDet.camera.ip}/ws/api/stream/${camera.type}`,
        // url: `ws://${camera.ip}/ws/api/stream/${camera.type}`,

        canvas: document.getElementById(`canvas-${camera.id}`),
      });

      console.log(this.cameraDet);
    },
  },
};
</script>
