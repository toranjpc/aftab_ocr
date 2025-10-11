<template>
  <v-app class="mamad-login-back">
    <div class="box">
      <div class="wave -one"></div>
      <div class="wave -two"></div>
      <div class="wave -three"></div>
    </div>
    <v-container class="fill-height">
      <v-row justify="center" class="text-center fill-height" align="center">
        <v-col cols="12" sm="7" md="4">
          <v-card outlined class="elevation-8 light rounded-lg pt-5">
            <v-card-title class="pb-0">
              <v-col justify="center" align="center" class="pb-0">
                <v-img
                  src="/logo.png"
                  max-width="180px"
                  class="mx-auto mb-1"
                  max-height="80px"
                  contain
                ></v-img>
              </v-col>
            </v-card-title>

            <v-card-text>
              <div class="font-weight-bold d-flex flex-column justify-center">
                <slot></slot>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    <alert />
  </v-app>
</template>

<script>
import Alert from '@/components/utilities/Alert'

export default {
  name: 'AuthTemplate',

  components: { Alert },
}
</script>

<style scoped>
#__layout .mamad-login-back {
  background: -webkit-linear-gradient(#25a7d7, var(--v-blueLogo-base));
  background: linear-gradient(#25a7d7, var(--v-blueLogo-base));
}
.back-color {
  background-color: rgba(212, 212, 212, 0.9) !important;
}

.border {
  text-shadow: -2px -2px 0 #fff, 2px -2px 0 #fff, -2px 2px 0 #fff,
    2px 2px 0 #fff;
}

body {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  overflow-y: hidden;
}

/*waves****************************/

.box {
  position: fixed;
  top: 0;
  transform: rotate(80deg);
  left: 0;
}

.wave {
  position: fixed;
  top: 0;
  left: 0;
  opacity: 1.4;
  position: absolute;
  top: 3%;
  left: 10%;
  background-color: var(--v-yellowLogo-lighten1);
  width: 1500px;
  height: 1300px;
  margin-left: -150px;
  margin-top: -250px;
  transform-origin: 50% 48%;
  border-radius: 43%;
  animation: drift 7000ms infinite linear;
}

.wave.-three {
  animation: drift 7500ms infinite linear;
  position: fixed;
  background-color: var(--v-yellowLogo-base);
}

.wave.-two {
  animation: drift 3000ms infinite linear;
  opacity: 0.1;
  background: black;
  position: fixed;
}

.box:after {
  content: '';
  display: block;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  z-index: 11;
  transform: translate3d(0, 0, 0);
}

@keyframes drift {
  from {
    transform: rotate(0deg);
  }
  from {
    transform: rotate(360deg);
  }
}

/*LOADING SPACE*/

/* .contain {
  animation-delay: 4s;
  z-index: 1000;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-flow: row nowrap;
  flex-flow: row nowrap;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;

  background-color: var(--v-yellowLogo-base);

  background: -webkit-linear-gradient(#25a7d7, #2962ff);
  background: linear-gradient(#25a7d7, #25a7d7);
} */

.icon {
  width: 100px;
  height: 100px;
  margin: 0 5px;
}

/*Animation*/
.icon:nth-child(2) img {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
}
.icon:nth-child(3) img {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
}
.icon:nth-child(4) img {
  -webkit-animation-delay: 0.4s;
  animation-delay: 0.4s;
}

.icon img {
  -webkit-animation: anim 2s ease infinite;
  animation: anim 2s ease infinite;
  -webkit-transform: scale(0, 0) rotateZ(180deg);
  transform: scale(0, 0) rotateZ(180deg);
}

@-webkit-keyframes anim {
  0% {
    -webkit-transform: scale(0, 0) rotateZ(-90deg);
    transform: scale(0, 0) rotateZ(-90deg);
    opacity: 0;
  }
  30% {
    -webkit-transform: scale(1, 1) rotateZ(0deg);
    transform: scale(1, 1) rotateZ(0deg);
    opacity: 1;
  }
  50% {
    -webkit-transform: scale(1, 1) rotateZ(0deg);
    transform: scale(1, 1) rotateZ(0deg);
    opacity: 1;
  }
  80% {
    -webkit-transform: scale(0, 0) rotateZ(90deg);
    transform: scale(0, 0) rotateZ(90deg);
    opacity: 0;
  }
}

@keyframes anim {
  0% {
    -webkit-transform: scale(0, 0) rotateZ(-90deg);
    transform: scale(0, 0) rotateZ(-90deg);
    opacity: 0;
  }
  30% {
    -webkit-transform: scale(1, 1) rotateZ(0deg);
    transform: scale(1, 1) rotateZ(0deg);
    opacity: 1;
  }
  50% {
    -webkit-transform: scale(1, 1) rotateZ(0deg);
    transform: scale(1, 1) rotateZ(0deg);
    opacity: 1;
  }
  80% {
    -webkit-transform: scale(0, 0) rotateZ(90deg);
    transform: scale(0, 0) rotateZ(90deg);
    opacity: 0;
  }
}
</style>
