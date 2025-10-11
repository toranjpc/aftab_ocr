<template>
  <span class="justify-center d-flex">
    <v-card style="width: fit-content" class="ma-3 py-4 px-4">
      <v-card-title class="px-0">
        <span>تغییر رمز</span>
      </v-card-title>
      <v-alert
        v-if="showAlert()"
        text
        outlined
        color="deep-orange"
        icon="mdi-fire"
      >
        شما موظف هستید که رمز عبور خود را تغییر دهید تا به بقیه قسمت‌های سایت
        دسترسی داشته باشید :)
      </v-alert>
      <v-card-text class="">
        <DynamicForm
          ref="dynamicForm"
          v-model="form"
          :fields="fields"
          :edit-item="editItem"
        />
        <span class="mx-2 px-2">
          <v-progress-linear
            v-if="form.new_pass"
            rounded
            :value="passwordStrengthPercent"
            :color="passwordStrengthColor"
            height="10"
          ></v-progress-linear>
          <p v-if="passwordStrength" class="pt-2 text-subtitle-1">
            قدرت رمز عبور:
            <b>
              {{ passwordStrength }}
            </b>
          </p>
        </span>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          class="px-10"
          small
          color="success"
          :disabled="
            passwordStrength !== 'خیلی قوی' && passwordStrength !== 'قوی'
          "
          @click="save"
        >
          ثبت
        </v-btn>
      </v-card-actions>
    </v-card>
  </span>
</template>

<script>
import { DynamicForm } from 'majra'
import fields from './fields'

export default {
  components: {
    DynamicForm,
  },

  layout: 'dashboard',

  withoutMiddleware: ['checkPass'],

  data() {
    return {
      dialog: false,
      fields: fields(this),
      form: {},
      editItem: {},
      password: '',
      passwordStrength: '',
      passwordStrengthPercent: 0,
      passwordStrengthColor: '',
    }
  },

  computed: {
    newPass() {
      return this.form.new_pass
    },
  },

  watch: {
    newPass: {
      immediate: false,
      handler(value, old) {
        if (value) this.checkPasswordStrength(value)

        console.log(value, old)
      },
    },
  },

  methods: {
    save() {
      if (
        this.form.new_pass === undefined ||
        this.form.pass === undefined ||
        this.form.new_pass_rep === undefined
      ) {
        this._event('alert', {
          text: 'تمامی مواد الزامی است لطفا پر کنید',
          color: 'error',
        })
        return 0
      }
      if (this.form.pass === this.form.new_pass) {
        this._event('alert', {
          text: 'رمز جدید نمی تواند مشابه رمز قدیمی باشد',
          color: 'error',
        })
        return 0
      }
      if (this.form.new_pass_rep !== this.form.new_pass) {
        this._event('alert', {
          text: 'رمز عبور جدید با تکرار آن مطابقت ندار',
          color: 'error',
        })
        return 0
      }
      this.$axios
        .post('/change-pass', {
          user_id: this.$auth.user.id,
          pass: this.form,
        })
        .then((sub) => {
          this._event('alert', {
            text: sub.data.message,
            color: 'success',
          })
        })
        .then((sub) => {
          this._event('alert', {
            text: sub.data.message,
            color: 'success',
          })
          this.showAlert(true)
        })
        .catch((err) => {
          this._event('alert', {
            text: err.response.data.message,
            color: 'error',
          })
        })
    },

    checkPasswordStrength(password) {
      const strength = this.calculatePasswordStrength(password)
      this.passwordStrength = strength.label
      this.passwordStrengthPercent = strength.percent
      this.passwordStrengthColor = strength.color
    },

    calculatePasswordStrength(password) {
      const strength = {
        label: 'ضعیف',
        percent: 20,
        color: 'red',
      }

      if (password.length > 6) {
        strength.label = 'متوسط'
        strength.percent = 50
        strength.color = 'orange'
      }

      const hasUpperCase = /[A-Z]/.test(password)
      const hasLowerCase = /[a-z]/.test(password)
      const hasNumbers = /[0-9]/.test(password)
      const hasSpecial = /[!@#\$%\^&\*]/.test(password)
      const hasPersian = /[\u0600-\u06FF]/.test(password)

      if (
        password.length > 8 &&
        (hasUpperCase || hasPersian) &&
        hasNumbers &&
        hasSpecial
      ) {
        strength.label = 'قوی'
        strength.percent = 80
        strength.color = 'yellow'
      }

      if (
        password.length > 10 &&
        (hasUpperCase || hasLowerCase || hasPersian) &&
        hasNumbers &&
        hasSpecial
      ) {
        strength.label = 'خیلی قوی'
        strength.percent = 100
        strength.color = 'green'
      }

      return strength
    },

    showAlert(v = false) {
      if (v) {
        return false
      }
      const lastPassChange = this.$auth.user.last_pass_change

      if (lastPassChange === null) return true
      const lastChangeDate = new Date(lastPassChange)

      // تاریخ فعلی
      const now = new Date()

      // محاسبه تفاوت زمانی به میلی‌ثانیه
      const timeDiff = now - lastChangeDate

      // تبدیل تفاوت زمانی به روز
      const daysDiff = timeDiff / (1000 * 60 * 60 * 24)
      if (daysDiff > 30 * 60) {
        return true
      }
      return false
    },
  },
}
</script>
