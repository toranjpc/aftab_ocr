<template>
  <auth-template>
    <v-card-text>
      <h2 class="mb-7 pb-12 font-weight-bold">ورود به سامانه آفتاب درخشان</h2>
      <login-form />
    </v-card-text>
  </auth-template>
</template>

<script>
import LoginForm from '@/components/auth/LoginForm'
import AuthTemplate from '@/components/auth/AuthTemplate'

export default {
  components: { AuthTemplate, LoginForm },

  middleware: 'guest',

  head: {
    title: 'ورود',
  },

  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
  ],
}
</script>

<style>
body,
.text-subtitle-2,
.v-btn__content,
.text-h1,
.text-h2,
.text-h3,
.text-h4,
.text-h5,
.text-h6,
.text-body-1,
.text-body-2,
.v-select.v-input--dense .v-select__selection--comma,
.v-messages__message,
.v-label,
.caption,
.text-subtitle-1,
.body-1,
.text-button,
.v-card__text,
.text-caption,
.text-overline,
.headline {
  font-family: 'IRANSans' !important;
}
</style>
