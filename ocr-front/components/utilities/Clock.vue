<template>
  <span class="px-2 d-none d-md-flex">{{ time }}</span>
</template>

<script>
export default {
  data: () => ({ time: '' }),

  created() {
    setInterval(() => {
      this.time = new Date().toLocaleString('fa-IR')
    }, 100)
  },
}
</script>
