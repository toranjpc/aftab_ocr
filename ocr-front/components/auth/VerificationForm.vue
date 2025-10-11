<template>
  <v-container fluid>
    <v-form class="mt-5 d-flex flex-column align-center justify-center">

      <v-progress-circular v-if="timer<120" class="mb-3" :value="timer*5/6"></v-progress-circular>

      <p>
        <span>کد ارسال شده2 به شماره</span>
        <span>{{ user.phone }}</span>
        <span>را وارد نمایید.</span>
      </p>
      <p>گاهی ممکن است ارسال پیام زمان بر باشد.</p>
      <v-text-field
        v-model="verificationCode"
        autofocus
        label="کد تایید"
        append-icon="mdi-cellphone-message"
      />
      <v-text-field
        v-model="password"
        outlined
        dense
        label="رمز"
        :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
        :type="show1 ? 'text' : 'password'"
        :rules="[validations.required(),validations.min(8)]"
        @click:append="show1 = !show1"
      />
      <v-text-field
        v-model="password_confirm"
        outlined
        dense
        label="تکرار رمز"
        :append-icon="show2 ? 'mdi-eye' : 'mdi-eye-off'"
        :type="show2 ? 'text' : 'password'"
        :rules="[validations.required(),validations.min(8)]"
        @click:append="show2 = !show2"
      />
      <v-col v-if="timer<120">
        <v-btn :loading="loading" color="primary" @click.prevent="verification({code:verificationCode})">
          ارسال
        </v-btn>
        <v-btn text color="primary" @click.prevent="backToRegister()">
          ویرایش اطلاعات
        </v-btn>
      </v-col>
      <v-col v-if="timer>120">
        <v-btn
          :loading="loading" class="my-2" color="warning"
          @click.prevent="resend({phone:user.phone})"
        >ارسال مجدد کد
        </v-btn>
        <v-btn text :loading="loading" color="primary" @click.prevent="backToRegister()">
          ویرایش شماره
        </v-btn>
      </v-col>

    </v-form>

  </v-container>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';
import validations from "~/helpers/validations";

export default {
  name: "VerificationForm",

  data() {
    return {
      validations,
      verificationCode: null,
      password: null,
      password_confirm: null,
      show1: false,
      show2: false,
    }
  },

  computed: {
    ...mapGetters({
      timer: 'authenticate/timer',
      loading: 'authenticate/loading',
      user: 'authenticate/user',
    })
  },

  methods: {
    ...mapActions({
      verification: 'authenticate/registerVerification',
      resend: 'authenticate/resend',
      backToRegister: 'authenticate/backToRegister',
    })
  },
}
</script>
