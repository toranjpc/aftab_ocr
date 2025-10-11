<template>
  <v-app class="set-iransas">
    <div class="d-flex flex-column col-12 pa-0">
      <div class="main-yellow" style="height: 4%; z-index: 2"></div>

      <div
        class="main-blue pt-6 pb-4 d-flex flex-column align-center"
        style="
          height: auto;
          position: relative;
          z-index: 3;
          box-shadow: 0px 0px 10px black;
        "
      >
        <v-img src="/white-logo-add.png" width="150px" class="mb-4" />
        <div
          class="main-yellow px-6 py-2 white--text font-weight-bold mt-2"
          style="
            border-radius: 1000px;
            bottom: -20px;
            position: absolute;
            border: 2px solid var(--v-mamadback-base);
          "
        >
          سامانه OCR
        </div>
      </div>

      <div style="width: 100%; position: relative"></div>

      <img src="/camerabg.jpg" id="background-video" />

      <div
        style="position: absolute; left: 0; top: 0; width: 100%; height: 100%"
        class="fade-video"
      ></div>

      <div class="mx-auto mt-7">
        <v-card elevation="10" style="border-radius: 15px">
          <v-card-text>
            <h2 class="font-weight-bold text-center">ورود</h2>
            <login-form />
          </v-card-text>
        </v-card>
      </div>
    </div>

    <Alert />
  </v-app>
</template>

<script>
import LoginForm from '@/components/auth/LoginForm'
import Alert from '~/components/utilities/Alert.vue'

export default {
  components: { LoginForm, Alert },
}
</script>

<style>
.main-blue {
  background-color: #2957a4;
}

.main-yellow {
  background-color: #ffcc2a;
}

#background-video {
  width: 100vw;
  height: 100vh;
  object-fit: cover;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
  /* z-index: -1; */
}

.fade-video {
  background: rgb(0, 6, 36);
  background: radial-gradient(
    circle,
    rgba(0, 6, 36, 0) 0%,
    rgba(255, 204, 42, 1) 100%,
    rgba(41, 87, 164, 1) 100%
  );
}
</style>
