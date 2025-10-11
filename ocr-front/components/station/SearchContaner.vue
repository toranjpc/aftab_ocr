<template>
  <span>
    <v-dialog v-model="dialog" max-width="500">
      <v-card id="trm">
        <v-card-title>
          <span>جستوجو شماره کانتینر</span>
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
              padding: 23px 15px 8px 15px;
              font-weight: bold;
              max-width: 292px;
              font-size: 24px;
              position: relative;
              height: 89px;
              flex-direction: row-reverse;
              justify-content: center;
              align-items: center;
              background-color: var(--v-blueLogo-base);
              border-radius: 5px;
            "
          >
            <v-text-field
              ref="nextFieldRef0"
              v-model="sss[0]"
              type="text"
              label=""
              dense
              flat
              dark
              solo-inverted
              hide-details
              style="min-width: 44px; max-width: 75px"
              @input="validateNumber0"
            ></v-text-field>

            <v-text-field
              ref="nextFieldRef1"
              v-model="sss[1]"
              type="number"
              dense
              flat
              dark
              solo-inverted
              hide-details
              style="
                margin-left: 5px;
                margin-right: 9px;
                min-width: 102px;
                max-width: 102px;
              "
              @input="validateNumber1"
            ></v-text-field>
            <v-text-field
              ref="nextFieldRef2"
              v-model="sss[2]"
              type="text"
              maxlength="1"
              dense
              flat
              dark
              solo-inverted
              hide-details
              @input="validateNumber2"
              style="max-width: 45px"
              @keyup.enter="update"
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
import { get as getSafe, clone } from 'lodash'

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
    }
  },
  created() {
    this._listen('SearchContanerNumber', (v) => {
      this.dialog = true
    })
  },
  methods: {
    reset() {
      this.acsept = 0
      this._event('SearchContaner', 0)
      this.dialog = false
      this.sss = []
    },
    update() {
      if (
        this.sss[0]?.length === 4 &&
        this.sss[1]?.length === 6 &&
        this.sss[2]?.length === 1
      ) {
        this.sss[2] = ',' + this.sss[2]
        this._event('SearchContaner', this.sss.join(''))
        this.dialog = false
        this.acsept = 1
        this.sss[2] = this.sss[2].substring(1, 2)
      } else if (
        (this.sss[0]?.length <= 4 || this.sss[0] === undefined) &&
        (this.sss[1]?.length <= 6 || this.sss[1] === undefined) &&
        (this.sss[2]?.length <= 1 || this.sss[2] === undefined)
      ) {
        this._event('SearchContaner', this.sss.join(''))
        this.dialog = false
        this.acsept = 1
      } else {
        this._event('alert', {
          text: 'شماره کانتینر به درستی وارد نشده است',
          color: 'error',
        })
      }
    },
    validateNumber0(value) {
      if (this.sss[0].length === 5) {
        this.sss[0] = this.sss[0] % 10
      }
      if (this.sss[0]?.length === 4) {
        this.$refs.nextFieldRef1.focus()
      }
    },
    validateNumber1() {
      if (this.sss[1].length === 7) {
        this.sss[1] = this.sss[1] % 10
      }
      if (this.sss[1]?.length === 6) {
        this.$refs.nextFieldRef2.focus()
      }
    },
    validateNumber2() {
      this.sss = clone(this.sss)
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

.v-autocomplete__content.v-menu__content::-webkit-scrollbar {
  width: 6px;
  background-color: var(--v-blueLogo-base) !important;
  box-shadow: inset 0px 0px 6px var(--v-application-base);
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
