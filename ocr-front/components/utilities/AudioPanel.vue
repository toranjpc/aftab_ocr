<template>
  <audio src="/sounds/online.mp3" ref="audio"></audio>
</template>

<script>
export default {
  mounted() {
    this._listen('play.audio', () => {
      this.$refs?.audio?.play()
    })
  }
}
</script>
