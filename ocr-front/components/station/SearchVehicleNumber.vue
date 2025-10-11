<template>
  <span>
    <v-dialog v-model="dialog" max-width="500">
      <v-card id="trm">
        <v-card-title>
          <span>جستوجو</span>
          <v-spacer />
          <v-btn color="error" icon @click="dialog = false">
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="mt-4 d-flex justify-center aline-center">
          <div
            class="d-flex mamad-plack"
            style="
              display: flex !important;
              background-size: cover;
              padding: 23px 5px 8px 28px;
              font-weight: bold;
              max-width: 292px;
              font-size: 24px;
              background-position: 50% 50%;
              position: relative;
              height: 89px;
              flex-direction: row-reverse;
              background-image: url(/img/pelakNormal.png);
              justify-content: center;
              align-items: center;
            "
            :style="`
              background-image: url(/img/${
                converto(sss[1]) === 'ع' ? 'pelak' : 'pelakNormal'
              }.png);
            `"
          >
            <v-text-field
              ref="nextFieldRef0"
              v-model="sss[0]"
              type="number"
              min="10"
              max="99"
              label=""
              dense
              flat
              solo-inverted
              hide-details
              style="min-width: 44px; max-width: 45px"
              @input="validateNumber0"
            ></v-text-field>

            <div id="letter-mamad">
              <v-autocomplete
                ref="nextFieldRef1"
                v-model="sss[1]"
                class="mx-1"
                item-text="text"
                item-value="value"
                placeholder="الف"
                dense
                flat
                solo-inverted
                hide-details
                :items="letter"
                style="min-width: 62px"
                @input="validateNumber1"
              ></v-autocomplete>
            </div>

            <v-text-field
              ref="nextFieldRef2"
              v-model="sss[2]"
              type="number"
              dense
              flat
              solo-inverted
              hide-details
              style="margin-right: 9px; min-width: 72px; max-width: 72px"
              @input="validateNumber2"
            ></v-text-field>
            <div></div>
            <v-text-field
              ref="nextFieldRef3"
              v-model="sss[3]"
              type="number"
              dense
              flat
              solo-inverted
              hide-details
              @input="validateNumber3"
              style="max-width: 45px"
            ></v-text-field>
          </div>
        </v-card-text>
        <v-card-actions style="display: flex; flex-direction: row">
          <v-spacer></v-spacer>
          <v-btn
            :disabled="!acsept"
            small
            class="px-6"
            color="error"
            @click="reset()"
          >
            خالی کردن این جستوجو
          </v-btn>
          <v-btn small class="px-6" color="success" @click="update()">
            جستوجو
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </span>
</template>

<script>
import { AbstractField } from 'majra'
export default {
  extends: AbstractField,

  data() {
    return {
      number: null,
      sss: [],
      loding: false,
      dialog: false,
      acsept: 0,
      item: null,
      letter: [
        { value: 'ein', text: 'ع' },
        { value: 'ta', text: 'ط' },
        { value: 'n', text: 'ن' },
        { value: 'sad', text: 'ص' },
        { value: 'q', text: 'ق' },
        { value: 'l', text: 'ل' },
        { value: 's', text: 'س' },
        { value: 'y', text: 'ی' },
        { value: 'h', text: 'ه' },
        { value: 'd', text: 'د' },
        { value: 'm', text: 'م' },
        { value: 'b', text: 'ب' },
      ],
    }
  },
  created() {
    this._listen('SearchVehicleNumber', (v) => {
      this.dialog = true
    })
  },
  methods: {
    reset() {
      this.acsept = 0
      this._event('SearchVehicle', 0)
      this.dialog = false
      this.sss = []
    },
    update() {
      if (
        this.sss[0]?.length === 2 &&
        this.sss[1]?.length > 0 &&
        this.sss[2]?.length === 3 &&
        this.sss[3]?.length === 2
      ) {
        this._event('SearchVehicle', this.sss.join(''))
        this.dialog = false
        this.acsept = 1
      } else
        this._event('alert', {
          text: 'پلاک به درستی وارد نشده است',
          color: 'error',
        })
    },
    validateNumber0() {
      if (this.sss[0].length === 3) {
        this.sss[0] = this.sss[0] % 10
      }
      if (this.sss[0] > 10 && this.sss[0] < 99) {
        this.$refs.nextFieldRef1.focus()
      }
    },
    validateNumber1() {
      if (this.sss[1].length) {
        this.$refs.nextFieldRef2.focus()
      }
    },
    validateNumber2() {
      if (this.sss[2].length === 4) {
        this.sss[2] = this.sss[2] % 100
      }
      if (this.sss[2] > 100 && this.sss[2] < 999) {
        this.$refs.nextFieldRef3.focus()
      }
    },
    validateNumber3() {
      if (this.sss[3].length === 3) {
        this.sss[3] = this.sss[3] % 10
      }
    },
    converto(t) {
      switch (t) {
        case 'ein':
          return 'ع'
        case 'ta':
          return 'ط'
        case 'n':
          return 'ن'
        case 'alef':
          return 'الف'
        case 'v':
          return 'و'
        case 'sad':
          return 'ص'
        case 'q':
          return 'ق'
        case 'l':
          return 'ل'
        case 's':
          return 'س'
        case 'y':
          return 'ی'
        case 'h':
          return 'ه'
        case 'd':
          return 'د'
        case 'm':
          return 'م'
        case 'b':
          return 'ب'
        default:
          return t
      }
    },
  },
}
</script>
<style scoped>
.v-input {
  justify-content: center;
  display: flex;
  align-items: center;
}
</style>
<style>
#trm input::-webkit-outer-spin-button,
#trm input::-webkit-inner-spin-button {
  display: none !important;
}
#trm input {
  direction: ltr;
  font-weight: bold;
}
/* .v-autocomplete__content.v-menu__content {
  min-width: 62px !important;
  scrollbar-width: 0px !important;
} */
.v-autocomplete__content.v-menu__content::-webkit-scrollbar {
  width: 6px;
  background-color: var(--v-blueLogo-base) !important;
  box-shadow: inset 0px 0px 6px var(--v-application-base);
}
#letter-mamad .v-input__slot {
  max-width: 62px;
}
#letter-mamad .v-input__append-inner {
  display: none !important;
}
.mamad-plack input {
  direction: ltr;
  text-align: center !important;
}
#letter-mamad input {
  direction: rtl;
  text-align: right !important;
}

.mamad-plack input::-webkit-outer-spin-button,
.mamad-plack input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
