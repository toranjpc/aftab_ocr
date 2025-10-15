<template>
  <v-form v-model="isValid" class="mt-5 mx-auto">
    <v-text-field
      v-model="form.username"
      outlined
      dense
      append-icon="fas fa-user"
      label="نام کاربری"
      :rules="phoneRules"
    />

    <v-text-field
      v-model="form.password"
      outlined
      dense
      label="رمز عبور"
      :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
      :type="show1 ? 'text' : 'password'"
      :rules="passwordRules"
      @click:append="show1 = !show1"
    />

    <v-img :src="image" class="rounded-lg" />

    <v-text-field
      v-model="form.captcha"
      class="mt-3"
      dense
      label="کد اعتبار سنجی"
      outlined
      @keyup.enter="login"
    />

    <v-btn
      :loading="loading"
      class="rounded-lg success"
      dark
      block
      @click.prevent="login"
    >
      <span>ورود</span>
      <v-icon right small>fal fa-arrow-left</v-icon>
    </v-btn>
    <!-- <v-btn class="mt-3" text block to="/reset-password">
      <v-icon left>fal fa-key</v-icon>
      <span>فراموشی رمز عبور</span>
    </v-btn> -->
  </v-form>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import validations from '@/helpers/validations'
import toEnglishDigits from '@/helpers/toEnglishDigits'

export default {
  name: 'LoginForm',

  data() {
    return {
      form: {
        username: '',
        password: '',
        key: '',
        captcha: '',
      },
      image: '',
      show1: false,
      isValid: false,
      phoneRules: [validations.required()],
      passwordRules: [validations.required()],
    }
  },

  computed: {
    ...mapGetters({
      errors: 'authenticate/errors',
      loading: 'authenticate/loading',
    }),
  },

  created() {
    this.getCaptcha()
    this._listen('captcha', () => {
      this.getCaptcha()
    })
    console.log(process.env.baseURL)
  },

  methods: {
    ...mapActions({
      LOGIN: 'authenticate/login',
    }),
    login() {
      this.isValid && this.LOGIN(this.form)
    },
    toEnglishDigits,
    getCaptcha() {
      this.$axios.$get('/captcha').then((res) => {
        this.form.key = res[0].key
        this.image = res[0].img
      })
    },
  },
}
</script>

<style>
body {
  height: 100vh;
  background-size: cover;
}
</style>
