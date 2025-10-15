<template>
  <div class="d-flex flex-column">
    <CameraWidget :col="6" :gate="this.matchGate" :matchGate="matchGate" />
    <CurrentLog :item="lastTruck" />

    <DynamicTemplate>
      <template #header-btn>
        <SseBtn :route="'api/sse/ocr-match?receiver_id=' + gateId" />
      </template>

      <template #item.plate_number="item">
        <EditBtn :editItem="item" :fields="plateFields(item)" @item-updated="handleItemUpdated(item)"
          v-if="['container_without_bijac', 'plate_without_bijac'].includes(item.match_status)" />
        <v-tooltip v-if="item.plate_number" top>
          <template v-slot:activator="{ on, attrs }">
            <div v-bind="attrs" v-on="on" v-html="plateShow(item.plate_number, item)">
            </div>
          </template>
          <span>{{ item.plate_number }}</span>
        </v-tooltip>
        <div v-else v-html="plateShow(item.plate_number, item)">
        </div>
      </template>

      <template #item.container_code="item">
        <EditBtn :editItem="item" :fields="containerFields(item)" @item-updated="handleItemUpdated(item)"
          v-if="['container_without_bijac', 'plate_without_bijac'].includes(item.match_status)" />
        <v-tooltip v-if="item.container_code" top>
          <template v-slot:activator="{ on, attrs }">
            <div v-bind="attrs" v-on="on" v-html="containerCodeShow(item.container_code, item)"></div>
          </template>
          <span style="direction:ltr">{{ item.container_code }}</span>
        </v-tooltip>
        <div v-else v-html="containerCodeShow(item.container_code, item)"></div>
      </template>

      <template #item.weight_customNb="item">
        <template v-if="item.type == 'gcoms'">
          <v-card class="ma-2" width="110" flat
            :color="item.total_weight < item.outed_weight ? '#e9b5c859' : '#008b8b1f'">
            <v-card-text class="text-center pa-2">
              <div class="d-flex flex-column align-center">
                <strong class="mb-1">{{ item.outed_weight }}</strong>

                <v-progress-linear :value="(item.outed_weight / item.total_weight) * 100" height="5" :reverse="true"
                  :color="item.total_weight === 0 ? '#d2d2d2' : item.total_weight < item.outed_weight ? 'red' : '#008b8b'"
                  :class="{ 'animate__animated animate__heartBeat animate__infinite': item.total_weight < item.outed_weight }"
                  rounded></v-progress-linear>
                <strong class="mt-2">{{ item.total_weight }}</strong>
              </div>
            </v-card-text>
          </v-card>
        </template>

        <template v-else-if="item.invoice">
          <v-card class="ma-2" width="110" flat :color="item.total_tu < item.ocr_tu ? '#e9b5c859' : '#bbb2101f'">
            <v-card-text class="text-center pa-2">
              <div class="d-flex flex-column align-center">
                <strong class="mb-1">{{ item.ocr_tu }}</strong>

                <v-progress-linear :value="(item.ocr_tu / item.total_tu) * 100" height="8" :reverse="true"
                  :color="item.total_tu === 0 ? '#d2d2d2' : item.total_tu < item.ocr_tu ? 'red' : '#bbb210'"
                  :class="{ 'animate__animated animate__heartBeat animate__infinite': item.total_tu < item.ocr_tu }"
                  rounded></v-progress-linear>
                <strong class="mt-2">{{ item.total_tu }}</strong>
              </div>
            </v-card-text>
          </v-card>
        </template>
      </template>

      <template #item.ocr_bijac="item">
        <VehicleCounterCard :item="item" />
      </template>

      <template #item.ocr_tu="item">
        <div v-if="item.type != 'gcoms' && item.invoice" style="direction: ltr;">
          {{ item.ocr_tu }} / {{ item.total_tu }}
        </div>
      </template>

      <template #item.match_status="item">
        <div class="d-flex p-0 m-0">

          <v-btn :color="renderBTN(item.match_status).color" dark @click="_event('ccs.dialog', item)"
            :data-id="item.id">
            <strong>
              {{ renderBTN(item.match_status).text }}
              <span v-if="item.invoices.length > 1" class="px-1 ms-1" style="border: 1px solid #fff;">{{
                item.invoices.length }}</span>
            </strong>
            <v-icon
              v-if="item.bijacs?.length > 0 && (new Date(item.log_time).getTime() - new Date(item.bijacs[0].bijac_date).getTime()) > (3 * 24 * 60 * 60 * 1000)"
              class="ms-2" color="red">
              mdi-alert-circle
            </v-icon>
          </v-btn>

          <v-btn small class="" color="danger mr-1" title=" تایید بیجک / فاکتور" @click="customCheck_confirm(item.id)"
            v-if="['container_without_bijac', 'plate_without_bijac'].includes(item.match_status) && !item.is_custom_check && !localConfirmed[item.id]">
            <v-icon class="" color="white">
              far fa-check
            </v-icon>
          </v-btn>
          <v-btn small class="" color="success mr-1" title="مدارک بررسی شده"
            v-else-if="['container_without_bijac', 'plate_without_bijac'].includes(item.match_status)">
            <v-icon class="" color="white">
              far fa-check
            </v-icon>
          </v-btn>
        </div>
      </template>

      <template #extra>
        <FactorDialog />
      </template>
    </DynamicTemplate>


    <template>
      <v-dialog v-model="confirmationDialog" max-width="400">
        <v-card>
          <v-card-title class="headline">تأیید عملیات</v-card-title>
          <v-card-text>
            آیا از تأیید بیجک/فاکتور برای شناسه **{{ itemIdToConfirm }}** اطمینان دارید؟
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" text @click="confirmationDialog = false">لغو</v-btn>
            <!-- این دکمه متد جدید ما را فراخوانی می کند -->
            <v-btn color="success" text @click="customCheck()">تأیید نهایی</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </template>


    <template>
      <div id="full-screen-overlay"></div>
      <img id="full-screen-image" src="" alt="Full Screen Image" />
    </template>
  </div>
</template>



<script>
import 'animate.css';
import { mapGetters } from 'vuex'
import { get as getSafe } from 'lodash'
import { DynamicTemplate } from 'majra'
import fields from '../matchFields'
import CurrentLog from '../currentLog.vue'
import SseBtn from '@/components/widgets/SseBtn'
import { getPermissions } from '@/helpers/helpers'
import CameraWidget from '@/components/widgets/CameraWidget.vue'
import FactorDialog from '~/components/truckLog/FactorDialog.vue'
import EditBtn from '@/components/utilities/EditBtn'
import PlateField from '@/components/utilities/PlateField'
import ContainerField from '@/components/utilities/ContainerField'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import VehicleCounterCard from '../VehicleCounterCard.vue';

export default {
  components: {
    DynamicTemplate,
    CameraWidget,
    SseBtn,
    FactorDialog,
    EditBtn,
    CurrentLog,
    VehicleCounterCard
  },

  layout: 'dashboard',

  data: () => ({
    dialog: false,
    gateId: 1,
    lastTruck: {},

    confirmationDialog: false,
    itemIdToConfirm: null,
    localConfirmed: {},

    matchGate: null,
  }),

  computed: {
    ...mapGetters({
      getItemsWithKey: 'dynamic/getItemsWithKey',
    }),

    gateName() {
      return {
        1: '1',
        2: '2',
        3: '3',
        4: '4',
      }[this.gateId]
    },

    items() {
      return this.getItemsWithKey('OcrMatch')
    },
  },

  watch: {
    items: {
      immediate: true,
      handler(value) {
        this.lastTruck = getSafe(value, '[0]', {})
      },
    },
  },

  created() {
    // this.$on('loading-changed', (isLoading) => {
    //   if (isLoading) {
    //     this.lastTruck = {};
    //   } else {
    //     this.lastTruck = getSafe(this.items, '[0]', {});
    //   }
    // });
    this.matchGate = this.$route.params.gate || 0;
    // console.log(this.matchGate)


    const hiddenActions = getPermissions.call(this)
    // const hiddenActions = ['delete', 'show', 'edit']

    this.$majra.init({
      hiddenActions,
      relations: [],
      fields: fields(this),
      mainRoute: {
        // route: '/ocr-match?_append=invoice_with=bijacs&filters[plate_number][$notNull]',
        route: '/ocr-match/list?_append=invoice_with=bijacs&gate=' + this.matchGate,
        key: 'OcrMatch',
      },
    })
  },

  mounted() {
    function showFullScreenImage(imageUrl) {
      var fullScreenImage = document.getElementById('full-screen-image')
      var fullScreenOverlay = document.getElementById('full-screen-overlay')

      fullScreenImage.src = imageUrl
      fullScreenImage.style.display = 'block'
      fullScreenOverlay.style.display = 'block'

      fullScreenImage.addEventListener('click', function () {
        fullScreenImage.style.display = 'none'
        fullScreenOverlay.style.display = 'none'
      })

      fullScreenOverlay.addEventListener('click', function () {
        fullScreenImage.style.display = 'none'
        fullScreenOverlay.style.display = 'none'
      })
    }

    function getEls() {
      const els = document.getElementsByClassName('resizable')
      if (els.length === 0) {
        return setTimeout(() => {
          getEls()
        }, 1000)
      }

      for (const el of els) {
        el.addEventListener('click', () => {
          showFullScreenImage(el.src)
        })
      }
    }

    if (this.$vuetify.breakpoint.mobile) {
      getEls()
    }

  },

  methods: {
    getSafe,

    plateFields: (item) => [
      {
        title: 'شماره پلاک',
        field: 'plate_number_edit',
        component: PlateField,
        normalize() {
          return item.plate_number
        },
      },
      {
        title: 'id',
        field: 'id',
        type: 'hidden',
      },
    ],
    containerFields: (item) => [
      {
        title: 'کد کانتینر',
        field: 'container_code_edit',
        component: ContainerField,
        type: 'text',
        normalize() {
          return item.container_code_edit ?? item.container_code
        },
      },
      {
        title: 'id',
        field: 'id',
        type: 'hidden',
      },
    ],

    containerCodeShow(v, form) {

      let concat = ''

      // if (form.container_code_2 && form.container_code_2 != v && !form.container_code_3)
      //   concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2, '#2957a4', form.container_code_3)

      // if (form.container_code_edit || v) {
      return (
        NormalizeContainerCodeAsImg(
          form.container_code_edit || v || null,
          form.container_code_edit ? 'green' : '#2957a4',
          form.container_code_3
        ) + concat
      )
      // }

      if (form.container_code_3) {
        return (
          NormalizeContainerCodeAsImg(
            form.container_code_3,
            '#2aa2db',
          )
        )
      }

      return '-'
    },

    plateShow(v, form) {

      let concat = ''

      // if (form.plate_number_2 && form.plate_number_2 != v && !form.plate_number_3)
      //   concat =
      //     '</br>' +
      //     NormalizeVehicleNumberAsImg(
      //       form.plate_number_2 || '',
      //       form.plate_type
      //     )

      // return (
      //   NormalizeVehicleNumberAsImg(
      //     form.plate_number_edit || v || form.plate_number_3 || '',
      //     form.plate_type,
      //     !!form.plate_number_edit,
      //     !!form.plate_image_url
      //   ) + concat
      // )

      return (
        NormalizeVehicleNumberAsImg(
          form.plate_number_edit || v,
          form.plate_type,
          !!form.plate_number_edit,
          form.plate_number_3
        ) + concat
      )
    },

    handleItemUpdated(updatedItem) {
      this.$store.commit('updateOcrMatchItem', updatedItem);
    },
    /*
      handleItemUpdated(updatedItem) {
        // پیدا کردن ایندکس آیتم به‌روزرسانی شده
        const index = this.items.findIndex(item => item.id === updatedItem.id);
  
        if (index !== -1) {
          // به‌روزرسانی آیتم در آرایه
          this.$set(this.items, index, updatedItem);
        }
      },
      */

    renderBTN(status) {

      const list = {
        // bad_match_nok: ['دو فاکتور متفاوت', 'purple'],
        gcoms_ok: ['فاکتور', 'cyan'],
        gcoms_nok: ['بدون فاکتور', 'red'],
        ccs_ok: ['فاکتور', 'green darken-4'],
        ccs_nok: ['بدون فاکتور', 'red'],
        container_without_bijac: ['بدون بیجک', 'orange'],
        plate_without_bijac: ['بدون بیجک', 'orange'],
        container_ccs_ok: ['فاکتور (کانتینر)', 'green'],
        container_ccs_nok: ['بدون فاکتور', 'red'],
        plate_ccs_ok: ['فاکتور (پلاک)', 'green'],
        plate_ccs_nok: ['بدون فاکتور', 'red'],
      }

      return {
        text: getSafe(list, status + '[0]', status),
        color: getSafe(list, status + '[1]', 'grey')
      }

    },

    async customCheck_confirm(itemId) {
      this.itemIdToConfirm = itemId;
      this.confirmationDialog = true;
    },
    async customCheck() {
      if (!this.itemIdToConfirm) {
        this._event("alert", { text: 'خطا: شناسه مورد نظر یافت نشد.', color: "red" });
        this.confirmationDialog = false;
        return;
      }
      try {
        this._event('loading')

        const res = await this.$axios.$post('/ocr-match/customCheck/' + this.itemIdToConfirm, {
          OcrMatch: this.form,
        })
        // this.$emit('item-updated', res.data)

        this.$set(this.localConfirmed, this.itemIdToConfirm, true)

        this._event('loading', false)
        this.confirmationDialog = false;

        this._event("alert", {
          text: 'تغییرات با موفقیت ذخیره شد',
          color: "green",
        });

      } catch (error) {
        this._event('loading', false);

        let errorMessage = "خطایی رخ داده است";

        if (error.response) {
          errorMessage = error.response.data.message || "خطای سرور";
        } else if (error.request) {
          errorMessage = "پاسخی از سرور دریافت نشد";
        }

        this._event("alert", {
          text: errorMessage,
          color: "red",
        });

      }
    },


  },


}
</script>

<style>
#full-screen-image {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  max-width: 100%;
  max-height: 100%;
  z-index: 9999;
  display: none;
}

#full-screen-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  z-index: 9998;
  display: none;
}

.v-card.v-card--hover .v-card__subtitle,
.v-card.v-card--hover .v-card__text {
  font-size: 1.1rem;
  line-height: 1.5rem;
}
</style>
