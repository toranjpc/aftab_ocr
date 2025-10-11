<template>
  <v-btn
    class="ml-2"
    icon
    :class="{ primary: autoRefresh }"
    @click="autoRefresh = !autoRefresh"
  >
    <v-icon small>fal fa-refresh</v-icon>
  </v-btn>
</template>

<script>
// import { initSSE } from '@/helpers/helpers'

export default {
  props: {
    route: {},
    callback: { default: () => {} },
  },

  data() {
    return {
      autoRefresh: true,
    }
  },

  watch: {
    autoRefresh(newVal) {
      if (newVal) {
        this.connectSSE();
      } else {
        this.$sse.disconnect(this.route);
      }
    }
  },

  mounted() {
    this._listen('autoRefresh', (state) => {
      this.autoRefresh = state
    })

     this.connectSSE();
  },

  beforeDestroy() {
    this.$sse.disconnect(this.route);
  },

  methods: {
    connectSSE() {
      if (!this.autoRefresh) return;
      
      this.$sse.connect(this.route, (data) => {
        this.$store.dispatch('dynamic/reloadMainData');
      });
    },
  }
}
</script>
