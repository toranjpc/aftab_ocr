import Vue from 'vue'
import { get as getSafe } from 'lodash'

const state = () => ({
  user: null,
  errors: {
    phone: '',
    password: '',
  },
  registerForm: null,
  loading: false,
  errorMessage: '',
  registerMode: true,
  resetMode: true,
  resetVerificationMode: false,
  timer: 0,
  showError: false,
  resetToken: null,
})

const getters = {
  user(state) {
    return state.user
  },
  loading(state) {
    return state.loading
  },
  errors(state) {
    return state.errors
  },
  errorMessage(state) {
    return state.errorMessage
  },
  registerMode(state) {
    return state.registerMode
  },
  timer(state) {
    return state.timer
  },
  showError(state) {
    return state.showError
  },
  resetMode(state) {
    return state.resetMode
  },
  resetVerificationMode(state) {
    return state.resetVerificationMode
  },
}

const mutations = {
  resetErrors(state) {
    state.errors = []
    state.errorMessage = ''
  },

  setErrors(state, payload) {
    state.errors = payload || []
  },

  setErrorMessage(state, payload) {
    state.errorMessage = payload || ''
  },

  setUser(state, payload) {
    state.user = payload
  },

  setShowError(state, payload) {
    state.showError = payload
  },

  setRegisterMode(state, payload) {
    state.registerMode = payload
  },

  setResetVerificationMode(state, payload) {
    state.resetVerificationMode = payload
  },

  setResetMode(state, payload) {
    state.resetMode = payload
  },

  setTimer(state, payload) {
    state.timer = payload
  },

  setLoading(state, payload) {
    state.loading = payload
  },

  setResetToken(state, payload) {
    state.resetToken = payload
  },

  setRegisterForm(state, payload) {
    state.registerForm = payload
  },
}

const actions = {
  login({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    this.$auth
      .loginWith('local', {
        data: payload,
      })
      .then((response) => {
        this.$router.push('/admin/dashboard')
      })
      .catch((error) => {
        Vue._event('captcha')
        Vue._event('alert', {
          text: getSafe(error, 'response.data.message', 'خطا در ورود'),
          color: 'error',
        })
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },

  register({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    commit('setRegisterForm', { ...payload })
    this.$axios
      .$post('/register', payload)
      .then((response) => {
        commit('setUser', response.user)
        commit('setRegisterMode', false)
        dispatch('resetTimer')
      })
      .catch((error) => {
        Vue._event('alert', { text: error.response.data.message, color: 'red' })
        commit('setErrorMessage', error.response.data.message)
        commit('setErrors', error.response.data.errors)
        commit('setShowError', true)
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },
  backToRegister({ commit }) {
    commit('setRegisterMode', true)
    // this.$router.unshift('/auth/r');
  },

  resend({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    this.$axios
      .$post('/user/reset-password', {
        phone: payload.phone,
      })
      .then((response) => {
        dispatch('resetTimer')
      })
      .catch((error) => {
        commit('setErrorMessage', error.response.data.message)
        commit('setShowError', true)
      })
  },

  registerVerification({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    this.$axios
      .$post('/user/verification', {
        phone: state.registerForm.phone,
        code: payload.code,
      })
      .then((response) => {
        dispatch('login', state.registerForm)
      })
      .catch((error) => {
        commit('setErrorMessage', error.response.data.message)
        commit('setShowError', true)
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },
  // reset password
  resetPassword({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    this.$axios
      .$post('/user/reset-password', payload)
      .then((response) => {
        commit('setResetMode', false)
        commit('setResetVerificationMode', true)
        commit('setUser', response.user)
        dispatch('resetTimer')
      })
      .catch((error) => {
        Vue._event('alert', {
          text: error?.response?.data?.message,
          color: 'error',
        })
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },

  resetVerification({ commit, state }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    this.$axios
      .$post('/user/verification', payload)
      .then((response) => {
        commit('setResetVerificationMode', false)
        Vue._event('alert', {
          text: 'رمز شما با موفقیت تغیر کرد',
          color: 'success',
        })
        setTimeout(() => {
          this.$router.push('/')
        }, 4000)
      })
      .catch((error) => {
        Vue._event('alert', {
          text: error?.response?.data?.message,
          color: 'error',
        })
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },

  changePassword({ commit, state, dispatch }, payload) {
    commit('resetErrors')
    commit('setLoading', true)
    this.$axios
      .$post('/change-password', {
        reset_token: state.resetToken,
        ...payload,
      })
      .then((response) => {
        dispatch('login', {
          phone: state.user.phone,
          password: payload.password,
        })
      })
      .catch((error) => {
        commit('setErrorMessage', error.response.data.message)
        commit('setErrors', error.response.data.errors)
        commit('setShowError', true)
      })
      .finally(() => {
        commit('setLoading', false)
      })
  },

  resetTimer({ state, commit }) {
    commit('setTimer', 0)
    const key = setInterval(() => {
      commit('setTimer', state.timer + 1)
      state.timer > 120 && clearInterval(key)
    }, 1000)
  },
}

export default {
  actions,
  mutations,
  getters,
  state,
}
