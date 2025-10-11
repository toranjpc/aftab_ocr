<template>
  <span @click="open = !open" style="cursor:pointer">{{ textShow }}</span>
</template>

<script>
export default {
  props: ["text", "length"],

  data: () => ({
    open: false
  }),

  computed: {
    textShow() {
      if (typeof this.text !== "string") return "";

      if (this.open) {
        return this.text;
      }
      return this.text.length > this.length
        ? this.text.slice(0, this.length) + " ..."
        : this.text;
    }
  }
};
</script>
