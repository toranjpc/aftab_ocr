<template>
  <div class="d-flex flex-row flex-wrap justify-center col-12">
    <div class="col-12 d-flex flex-row flex-wrap justify-center align-start">
      <div class="col-12 col-md-3 pt-0 d-flex flex-column">
        <v-card min-width="31%"
          class="d-flex py-7 pa-4 elevent-0 ma-2 flex-column text-center justify-center d-flex align-center mx-1"
          color="#fff" outlined>
          <v-avatar dark color="#ede9fe" class="" size="64">
            <v-icon color="#8f62f6 !important">
              fal fa-gallery-thumbnails
            </v-icon>
          </v-avatar>
          <v-btn icon style="position: absolute; left: 18px; top: 14px" @click="getMoves()">
            <i class="fa fa-refresh text-gray" style="color: gray"></i>
          </v-btn>

          <div class="d-flex flex-column justify-center mt-1">
            <span class="font-weight-bold">
              {{ movement }}
            </span>
            <span>کل تردد ساعت پیش</span>
            {{ gettime() }}
          </div>
        </v-card>
        <SingleCameraWidget :gate="matchGate" :plate="true" :label="false" :matchGate="matchGate" />

        <TruckListMinimal :fields="truckFields" @select="selectTruck" />

      </div>

      <div class="col-12 col-md-9 pa-0">
        <CardWidget id="padding-low" :title="statusMessage(selectedTruck)" style="border: 2px solid white"
          :style="{ outline: '5px solid ' + statusColor(selectedTruck) }">
          <template #actions>
            <div class="mx-1" style="width: 200px"
              v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)">
              <v-text-field v-model="receiptNumber" label="شماره قبض انبار" hide-details dense rounded outlined
                append-icon="fal fa-check" @click:append="findBy({ receipt_number: receiptNumber })" />
            </div>

            <div class="mx-1" style="width: 200px"
              v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)">
              <v-text-field v-model="bijac" label="شماره بیجک" hide-details dense rounded outlined
                append-icon="fal fa-check" @click:append="findBy({ bijac_number: bijac })" />
            </div>


            <div class="px-4">
              <v-btn small class="" color="danger mr-1" title=" تایید بیجک / فاکتور"
                @click="customCheck_confirm(selectedTruck.id)"
                v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status) && !selectedTruck.is_custom_check && !localConfirmed[selectedTruck.id]">
                <v-icon class="" color="white">
                  far fa-check
                </v-icon>
              </v-btn>
              <v-btn small class="" color="success mr-1" title="مدارک بررسی شده"
                v-else-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)">
                <v-icon class="" color="white">
                  far fa-check
                </v-icon>
              </v-btn>

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
            </div>


            <div v-if="selectedTruck.plate_number" class="px-4">
              <EditBtn v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)"
                :editItem="selectedTruck" :fields="plateFields(selectedTruck)" @update="reloadMainData" />
              <div v-html="plateShow(selectedTruck.plate_number, selectedTruck)"></div>
            </div>

            <div v-if="selectedTruck.container_code" class="px-4">
              <EditBtn v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)"
                :editItem="selectedTruck" :fields="containerFields(selectedTruck)" @update="reloadMainData" />
              <div style="transform: scale(0.7)" v-html="containerCodeShow(selectedTruck.container_code, selectedTruck)
                "></div>
            </div>

            <LogHistory :ocr-log="selectedTruck" />

            <component :is="comp" :route="'api/sse/ocr-match?receiver_id=' + matchGate" />
          </template>
          <v-card-text class="pb-0 pa-0 d-flex flex-row flex-wrap">
            <div class="col-12 d-flex flex-wrap flex-row pa-0">
              <div class="col-12 pa-0">
                <TruckImages :truck="selectedTruck" />
              </div>

              <div v-if="getSafe(selectedTruck, 'bijacs[0].type') === 'gcoms'" class="col-12 pa-0 mt-2">
                <GcomsInvoiceSearchPanel :fields="gcomsFields" :activePlateNumber="selectedTruck" />
              </div>

              <div class="col-12 mt-8 pa-0">
                <TruckBijacs :truck="selectedTruck" :bijacFields="bijacFields" />
              </div>

              <div class="col-12 mt-8 d-flex flex-column">
                <TruckInvoice :truck="selectedTruck" :invoiceFields="invoiceFields" />
              </div>
            </div>
          </v-card-text>
        </CardWidget>
      </div>
    </div>

    <DynamicTemplate class="mt-2" style="display: none"></DynamicTemplate>

    <GcomsTruckData :truck="selectedTruck" />
  </div>
</template>

<script>
import fields from './fields'
import { mapGetters } from 'vuex'
import { get as getSafe } from 'lodash'
import { DynamicTemplate } from 'majra'
import SseBtn from '@/components/widgets/SseBtn'
import truckHelpers from '@/helpers/truckHelper.js'
import EditBtn from '@/components/utilities/EditBtn'
import CardTitle from '~/components/widgets/CardTitle.vue'
import PlateField from '@/components/utilities/PlateField'
import CardWidget from '~/components/widgets/CardWidget.vue'
import LogHistory from '~/components/monitoring/LogHistory.vue'
import TruckImages from '~/components/monitoring/TruckImages.vue'
import TruckBijacs from '~/components/monitoring/TruckBijacs.vue'
import TruckInvoice from '~/components/monitoring/TruckInvoice.vue'
import GcomsTruckData from '~/components/monitoring/GcomsTruckData.vue'
import TruckListMinimal from '~/components/truckLog/TruckListMinimal.vue'
import SingleCameraWidget from '~/components/widgets/SingleCameraWidget.vue'
import { invoiceFields, truckFields, bijacFields, gcomsFields } from './fields'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import GcomsInvoiceSearchPanel from '~/components/gcoms/GcomsInvoiceSearchPanel.vue'

export default {
  components: {
    SseBtn,
    EditBtn,
    CardTitle,
    CardWidget,
    LogHistory,
    TruckImages,
    TruckBijacs,
    TruckInvoice,
    GcomsTruckData,
    DynamicTemplate,
    TruckListMinimal,
    SingleCameraWidget,
    GcomsInvoiceSearchPanel,
  },
  layout: 'dashboardSimple',

  data() {
    return {
      bijac: '',
      movement: 0,
      comp: 'div',
      truckFields,
      bijacFields,
      invoiceFields,
      receiptNumber: '',
      autoRefresh: true,
      selectedTruck: {},
      gcomsFields: gcomsFields(this),

      confirmationDialog: false,
      itemIdToConfirm: null,
      localConfirmed: {},
      matchGate: null,
    }
  },

  computed: {
    ...mapGetters({
      getItemsWithKey: 'dynamic/getItemsWithKey',
    }),
    items() {
      return this.getItemsWithKey('OcrMatch')
    },
  },

  watch: {
    items: {
      immediate: true,
      handler(value) {
        const latestTruck = getSafe(value, '[0]', false)

        if (!this.autoRefresh) return

        this.selectedTruck = latestTruck
      },
    },
  },

  created() {
    this.matchGate = this.$route.params.id || 0;


    this.getMoves()
    this.$majra.init({
      hiddenActions: [
        'edit',
        'show',
        'create',
        'delete',
        'filter',
        'printer',
        'download',
      ],
      mainRoute: {
        route: `/ocr-match?itemPerPage=10&_append=invoice&_with=bijacs&_with=isCustomCheck&gate_number=${this.matchGate}&plate_type=iranian,iran,afghan`,
        key: 'OcrMatch',
      },
      fields: fields(this),
    })

    this._listen('templateMounted', () => {
      this.comp = SseBtn
    })

    this._listen('selected.truck.change', (truck) => {
      this.selectedTruck = truck
    })
  },

  methods: {
    getSafe,
    ...truckHelpers,
    gettime() {
      // دریافت زمان فعلی
      const now = new Date()

      // دریافت ساعت فعلی
      const currentHour = now.getHours()

      // محاسبه ساعت قبلی
      const previousHour = currentHour === 0 ? 23 : currentHour - 1 // اگر ساعت 0 باشد، ساعت قبلی 23 است

      // برگرداندن بازه‌ی زمانی به صورت رشته
      return `${previousHour}-${currentHour}`
    },

    getMoves() {
      const [s, e] = this.getRoundedHourRange() // دریافت بازه زمانی

      this.$axios
        .$post(
          `/log/rip?_with=bijacs,bijacs.invoice&filters[log_time][$between][0]=${s}&filters[log_time][$between][1]=${e}&filters[plate_number][$notNull]&disable_all=true`
        )
        .then((res) => {
          this.movement = res.counts.all
        })
        .catch((error) => {
          console.error('خطا در دریافت داده‌ها:', error)
        })
    },

    getRoundedHourRange() {
      const now = new Date()

      // حذف دقیقه، ثانیه و میلی‌ثانیه برای گرد کردن به ساعت
      now.setMinutes(0, 0, 0)

      // محاسبه یک ساعت قبل
      const oneHourAgo = new Date(now)
      oneHourAgo.setHours(now.getHours() - 1)

      // تابع تبدیل تاریخ به فرمت موردنظر + encode
      const formatDate = (date) => {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        const hours = String(date.getHours()).padStart(2, '0')

        // فرمت صحیح برای دیتابیس و API: YYYY-MM-DD HH:mm
        const formatted = `${year}-${month}-${day} ${hours}:00`

        // تبدیل به URL Encode
        return encodeURIComponent(formatted)
      }

      return [formatDate(oneHourAgo), formatDate(now)]
    },

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
        type: 'text',
        normalize() {
          return item.container_code
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

      if (form.container_code_2 && form.container_code_2 != v)
        concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2)

      if (v) {
        return (
          NormalizeContainerCodeAsImg(
            form.container_code_edit || v,
            form.container_code_edit ? 'green' : '#2957a4'
          )
        )
      }

      return '-'
    },

    plateShow(v, form) {
      let concat = ''

      if (form.plate_number_2 && form.plate_number_2 != v)
        concat =
          '</br>' +
          NormalizeVehicleNumberAsImg(
            form.plate_number_2 || '',
            form.plate_type
          )

      return (
        NormalizeVehicleNumberAsImg(
          form.plate_number_edit || v || '',
          form.plate_type,
          !!form.plate_number_edit
        ) + concat
      )
    },

    reloadMainData() {
      this._event('paginate')
    },

    findBy(params) {
      this._event('loading')
      this.$axios
        .$post('/check-by', {
          params,
          id: this?.selectedTruck?.id,
        })
        .then((res) => {
          if (res.message === 'not found') {
            this._event('alert', { text: 'موردی یافت نشد' })
          } else {
            this._event('alert', { text: 'در حال لود مجدد' })
          }
          this._event('paginate')
        })
        .catch(() => {
          this._event('alert', { text: 'مجدد تلاش کنید' })
        })
        .finally(() => {
          this.bijac = ''
          this.receiptNumber = ''
          this._event('loading', false)
        })
    },

    selectTruck(truck) {
      this.selectedTruck = truck;

      if (truck.bijacs.length === 0) {
        this._event('alert', { text: 'بیجکی یافت نشد' })
      } else {
        this._event('alert', { text: 'دارای بیجک' })
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
#padding-low>.v-card__title {
  padding: 8px !important;
}
</style>
