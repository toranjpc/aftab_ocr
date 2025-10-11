<template>
  <div
    class="text-center"
    :class="{ 'white--text': getProp ? getProp('dark', false) : false }"
  >
    <span>کد ارسالی تا </span>
    <span>{{ time }}</span>
    <span> ثانیه دیگر اعتبار دارد</span>
  </div>
</template>

<script>
import { AbstractField } from "majra";

export default {
  extends: AbstractField,

  data: () => ({
    time: 120,
  }),

  created() {
    this._listen("resetCountDown", () => {
      this.time = 120;
      this.init();
    });
    this.init();
  },

  methods: {
    init() {
      const interval = setInterval(() => {
        if (this.time === 0) {
          this._event("countDownEnd");
          return clearInterval(interval);
        }
        this.time -= 1;
      }, 1000);
    },
  },
};
</script>
