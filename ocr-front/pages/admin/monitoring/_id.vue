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

          <div class="d-flex flex-column justify-center mt-1" style="width: 100%;">

            <table class="my-4">
              <thead>
                <th>کل تردد های گیت</th>
                <th>تردد های این گیت</th>
                <th>تردد های این ساعت</th>
              </thead>
              <tbody>
                <td>{{ gate_collection }}</td>
                <td>{{ gate_count }}</td>
                <td>{{ gate_count_last_hour }}</td>
              </tbody>
            </table>

            <!-- <span>کل تردد ساعت پیش</span>
            {{ gettime() }} -->
          </div>
        </v-card>
        <SingleCameraWidget :gate="matchGate" :plate="true" :label="false" :matchGate="matchGate" />

        <TruckListMinimal :fields="truckFields" @select="selectTruck" :matchGate="matchGate" :page.sync="page" />

      </div>

      <div class="col-12 col-md-9 pa-0">

        <div class="popplace back"
          style="position: fixed;top: 0;left: 0;right: 0;bottom: 0;z-index: 1;background-color: #ffffffa6;"
          v-if="selectedTruckBase && selectedTruckBase.id">
          <div class="shadow" @click="clearSelectedTruck" style="width: 100%;height: 100%;margin: 0;padding: 0; "></div>
          <CardWidget class="mb-4" id="padding-low" :title="statusMessage(selectedTruckBase)"
            style="position: fixed;width: 80%;left: 5%;top: 10%;right: 10%;z-index: 1;height: unset;"
            :style="{ outline: '5px solid ' + statusColor(selectedTruckBase) }">
            <template #actions>
              <div class="d-flex">

                <div class="flex-1" style="">
                  <v-btn
                    v-if="['container_without_bijac', 'plate_without_bijac', 'plate_without_bijac_Creq'].includes(selectedTruckBase.match_status)"
                    small class="" color="success mr-1" @click="fatabInvoiceCheck = true">
                    کانتینر خالی
                  </v-btn>
                </div>

                <div class="d-flex flex-1" style="width: 200px"
                  v-if="!selectedTruckBase.bijacs.length && ['container_without_bijac', 'plate_without_bijac', 'plate_without_bijac_Creq'].includes(selectedTruckBase.match_status)">
                  <div class="flex-1" style="">
                    <v-text-field v-model="bijac" label="شماره بیجک" hide-details dense rounded outlined
                      append-icon="fal fa-check"
                      @click:append="findBy({ bijac_number: bijac })" /><!-- :disabled="selectedTruckBase?.is_serach_bijac" -->
                  </div>
                </div>

                <div class="d-flex flex-1" style="width: 300px"
                  v-if="(selectedTruckBase?.is_serach_bijac && !selectedTruckBase.bijacs.length)">
                  <!-- selectedTruckBase.match_status.includes('without_bijac')) || selectedTruckBase.match_status.includes('_nok') -->

                  <div class="flex-4" style="width: 70%">
                    <v-text-field v-model="receiptNumber" label="شماره قبض انبار" hide-details dense rounded outlined
                      append-icon="fal fa-check" @click:append="findBy({ receipt_number: prefix + receiptNumber })" />
                  </div>

                  <div class="flex-1" style="width: 30%">
                    <v-select v-model="prefix" :items="prefixes"></v-select>
                  </div>

                </div>
              </div>
              <!--

              <div class="px-4">

              <v-btn small class="" color="danger mr-1" title=" تایید بیجک / فاکتور"
                @click="customCheck_confirm(selectedTruckBase.id)"
                v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruckBase.match_status) && !selectedTruckBase?.is_custom_check && !localConfirmed[selectedTruckBase.id]">
                <v-icon class="" color="white">
                  far fa-check
                </v-icon>
              </v-btn>
              <v-btn small class="" color="success mr-1" title="مدارک بررسی شده"
                v-else-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruckBase.match_status)">
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
                        <v-btn color="success" text @click="customCheck()">تأیید نهایی</v-btn>
                      </v-card-actions>
                    </v-card>
                  </v-dialog>
                </template>

</div>
-->


            <div v-if="selectedTruckBase.plate_number" class="px-4">
              <EditBtn
                v-if="selectedTruckBase.match_status && ['container_without_bijac', 'plate_without_bijac'].includes(selectedTruckBase.match_status)"
                :editItem="selectedTruckBase" :fields="plateFields(selectedTruckBase)" @update="reloadMainData" />
              <div v-html="plateShow(selectedTruckBase.plate_number, selectedTruckBase)"></div>
            </div>

            <div v-if="selectedTruckBase.container_code" class="px-4">
              <EditBtn
                v-if="selectedTruckBase.match_status && ['container_without_bijac', 'plate_without_bijac'].includes(selectedTruckBase.match_status)"
                :editItem="selectedTruckBase" :fields="containerFields(selectedTruckBase)" @update="reloadMainData" />
              <div style="transform: scale(0.7)" v-html="containerCodeShow(selectedTruckBase.container_code, selectedTruckBase)
                "></div>
            </div>

              <!-- <LogHistory :ocr-log="selectedTruckBase" /> -->

              <v-btn icon class="ml-2 v-btn v-btn--icon v-btn--round theme--light v-size--default danger"
                @click="clearSelectedTruck" style="position: absolute; left: -25px; top: -25px;">
                <i class="fa fa-times" style="color: #fff"></i>
              </v-btn>

            </template>
            <v-card-text class="pb-0 pa-0 d-flex flex-row flex-wrap">
              <div class="col-12 d-flex flex-wrap flex-row pa-0">
                <div class="col-12 pa-0">
                  <TruckImages :truck="selectedTruckBase" />
                </div>

                <div v-if="getSafe(selectedTruckBase, 'bijacs[0].type') === 'gcoms'" class="col-12 pa-0 mt-2">
                  <GcomsInvoiceSearchPanel :fields="gcomsFields" :activePlateNumber="selectedTruckBase" />
                </div>

                <div class="col-12 mt-8 pa-0">
                  <TruckBijacs :truck="selectedTruckBase" :bijacFields="bijacFields" />
                </div>

                <div class="col-12 mt-8 d-flex flex-column">
                  <TruckInvoice :truck="selectedTruckBase" :invoiceFields="invoiceFields" />
                </div>
              </div>
            </v-card-text>
          </CardWidget>
        </div>


        <template>
          <v-dialog v-model="fatabInvoiceCheck" max-width="400">
            <v-card>
              <v-card-title class="headline">ثبت کانتینر خالی</v-card-title>

              <v-card-text>
                <v-text-field v-model="invoiceAftab" label="شماره فاکتور/قبض انبار" hide-details="auto" dense rounded
                  outlined append-icon="fal fa-check" :error="aftabError" :error-messages="aftabErrorMessage"
                  @click:append="searchInvoiceAftab(invoiceAftab)" />
              </v-card-text>

              <v-card-text v-html="empinvoicetext"></v-card-text>

              <v-card-actions class="d-flex justify-space-between px-4 pb-4">
                <v-btn v-if="fatabInvoiceCheck20tu" vlarge color="primary" class="flex-grow-1 mr-2"
                  style="font-size: 1.5em;" @click="selectContainer('_20Feet')">
                  20Tu
                </v-btn>

                <v-btn v-if="fatabInvoiceCheck40tu" large color="success" class="flex-grow-1 ml-2"
                  style="font-size: 1.5em;" @click="selectContainer('_40Feet')">
                  40Tu
                </v-btn>
              </v-card-actions>

            </v-card>
          </v-dialog>
        </template>


        <CardWidget id="padding-low" :title="statusMessage(selectedTruck)" style="border: 2px solid white"
          :style="{ outline: '5px solid ' + statusColor(selectedTruck) }">
          <template #actions>

            <!--
            
            <div class="mx-1" style="width: 200px"
              v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)">
              <v-text-field v-model="bijac" label="شماره بیجک" hide-details dense rounded outlined
                append-icon="fal fa-check"
                @click:append="findBy({ bijac_number: bijac })" />
            </div>


            <div class="mx-1" style="width: 200px"
              v-if="selectedTruck?.is_serach_bijac && ['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)">
              <v-text-field v-model="receiptNumber" label="شماره قبض انبار" hide-details dense rounded outlined
                append-icon="fal fa-check" @click:append="findBy({ receipt_number: receiptNumber })" />
            </div>


            <div class="px-4">
              <v-btn small class="" color="danger mr-1" title=" تایید بیجک / فاکتور"
                @click="customCheck_confirm(selectedTruck.id)"
                v-if="['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status) && !selectedTruck?.is_custom_check && !localConfirmed[selectedTruck.id]">
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
                      <v-btn color="success" text @click="customCheck()">تأیید نهایی</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </template>
  </div>
  -->


            <div v-if="selectedTruck.plate_number" class="px-4">
              <EditBtn
                v-if="selectedTruck.match_status && ['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)"
                :editItem="selectedTruck" :fields="plateFields(selectedTruck)" @update="reloadMainData" />
              <div v-html="plateShow(selectedTruck.plate_number, selectedTruck)"></div>
            </div>

            <div v-if="selectedTruck.container_code" class="px-4">
              <EditBtn
                v-if="selectedTruck.match_status && ['container_without_bijac', 'plate_without_bijac'].includes(selectedTruck.match_status)"
                :editItem="selectedTruck" :fields="containerFields(selectedTruck)" @update="reloadMainData" />
              <div style="transform: scale(0.7)" v-html="containerCodeShow(selectedTruck.container_code, selectedTruck)
                "></div>
            </div>

            <LogHistory :ocr-log="selectedTruck" />

            <component :is="comp" :route="`api/sse/ocr-match?receiver_id=${matchGate}&gate_number=${matchGate}`" />
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
      groupGate: 0,
      thisGate: 0,
      last_hour: 0,

      gate_count: 0,
      gate_count_last_hour: 0,
      gate_collection: 0,

      comp: 'div',
      truckFields,
      bijacFields,
      invoiceFields,
      receiptNumber: '',
      autoRefresh: true,
      selectedTruck: {},
      selectedTruckBase: {},
      AllTrucks: [],
      gcomsFields: gcomsFields(this),

      confirmationDialog: false,
      itemIdToConfirm: null,
      localConfirmed: {},
      matchGate: null,
      page: 1,
      prefixes: ['BSRCC', 'BSRGCBI'],
      prefix: 'BSRCC',

      invoiceAftab: '',
      fatabInvoiceCheck: false,
      fatabInvoiceCheck20tu: false,
      fatabInvoiceCheck40tu: false,
      aftabError: false,
      aftabErrorMessage: '',
      findInvoice: 0,
      empinvoicetext: '',

    }
  },
  mounted() {

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
        this.AllTrucks = structuredClone(value)

        const updated = this.AllTrucks.find(t => t.id === this.selectedTruckBase.id)
        if (updated) {
          this.selectedTruckBase = JSON.parse(JSON.stringify(updated))
        } else {
          this.clearSelectedTruck()
        }

        const latestTruck = getSafe(value, '[0]', false)
        // console.log(this.AllTrucks)
        if (!this.autoRefresh) return

        this.selectedTruck = latestTruck

        this.getMoves()

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
        route: `/ocr-match?itemPerPage=10&_append=invoice&_with=bijacs&_with=isCustomCheck&_with=isSerachBijac&gate_number=${this.matchGate}&plate_type=iranian,iran,afghan`,
        key: 'OcrMatch',
      },
      fields: fields(this),
    })

    this._listen('templateMounted', () => {
      this.comp = SseBtn
    })

    this._listen('selected.truck.change', (truck) => {
      // this.selectedTruckBase = truck
      // window.scrollTo({ top: 0, behavior: 'smooth' });

      // this.selectedTruck = truck
    })
  },

  methods: {
    getSafe,
    ...truckHelpers,
    gettime() {
      const now = new Date()
      const currentHour = now.getHours()
      const previousHour = currentHour === 0 ? 23 : currentHour - 1 // اگر ساعت 0 باشد، ساعت قبلی 23 است
      return `${previousHour}-${currentHour}`
    },

    clearSelectedTruck() {
      this.selectedTruckBase = {};

      this.invoiceAftab = ''
      this.fatabInvoiceCheck = false
      this.fatabInvoiceCheck20tu = false
      this.fatabInvoiceCheck40tu = false
      this.aftabError = false
      this.aftabErrorMessage = ''
      this.findInvoice = 0
      this.empinvoicetext = ''
    },

    getMoves() {
      const [s, e] = this.getRoundedHourRange() // دریافت بازه زمانی

      this.$axios
        .$post(
          `/log/gateCounter?&gate_number=${this.matchGate}`
        )
        .then((res) => {
          const east_ = [2, 3, 4];
          // this.movement = res.all
          this.gate_count = res.gate_count
          this.gate_count_last_hour = res.gate_count_last_hour
          this.gate_collection = res.gate_collection

        })
        .catch((error) => {
          // console.error('خطا در دریافت داده‌ها:', error)
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
      this.$majra.reload()
      // this._event('paginate')
    },

    findBy(params) {
      if (!params.bijac_number && !params.receipt_number) {
        this._event('alert', { text: 'لطفا فیلد مورد نیاز را پر نمایید' })
        return false
      }
      this._event('loading')
      this.$axios
        .$post('/check-by', {
          params,
          id: this?.selectedTruckBase?.id,
        })
        .then((res) => {
          console.log(res)
          if (res.message === 'success') {
            this._event('alert', { text: 'عملیات انجام شد. درحال بارگزاری اطلاعات ...' })
          } else if (res.message === "bijac_has_invoice") {
            this._event('alert', { text: 'فاکتور قبلا ثبت شده است' })
          } else if (res.message === "receipt_number is wrong") {
            this._event('alert', { text: 'شماره قبض انبار اشتباه می باشد' })
          } else if (res.message === "err") {
            this._event('alert', { text: 'خطایی رخ داده' })
          } else {
            this._event('alert', { text: 'موردی یافت نشد' })
          }

          this._event('autoRefresh', false)
          this.$store.dispatch('dynamic/get', {
            page: this.page,
            key: 'OcrMatch',
          })

          const updated = this.AllTrucks.find(t => t.id === this.selectedTruckBase.id)
          if (updated) {
            this.selectedTruckBase = JSON.parse(JSON.stringify(updated))
          } else {
            this.clearSelectedTruck()
          }

          // this.$axios
          //   .$get(`/ocr-match?itemPerPage=10&_append=invoice&_with=bijacs&_with=isCustomCheck&_with=isSerachBijac&findThis=${this?.selectedTruckBase?.id}&gate_number=${this.matchGate}&plate_type=iranian,iran,afghan&id=${truck.id}`)
          //   .then((res) => {
          //     if (res && res.length > 0) {
          //       this.selectedTruckBase = { ...res[0], is_serach_bijac: 1 };
          //     }
          //   })
          //   .catch((err) => {
          //     // console.error("خطا در دریافت داده:", err);
          //     // this._event('alert', { text: 'خطا در دریافت داده‌ها', color: 'red' });
          //   });

          this._event('paginate')
        })
        .catch((res) => {
          // console.log("error : ", res)

          this._event('alert', { text: 'مجدد تلاش کنید' })
        })
        .finally(() => {
          this.bijac = ''
          this.receiptNumber = ''
          this._event('loading', false)
        })
    },

    selectTruck(truck) {
      this.selectedTruckBase = truck;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      console.log(this.selectedTruckBase)
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

    async searchInvoiceAftab(data = 0) {
      this.findInvoice = 0
      this.fatabInvoiceCheck20tu = false
      this.fatabInvoiceCheck40tu = false
      if (!data) return false

      this._event('loading')
      this.empinvoicetext = ''
      await this.$axios
        .$post('/findAftabInvoice', {
          data,
          selectedTruckBase: this.selectedTruckBase.id,
        })
        .then((res) => {
          console.log(res)
          if (res && res.status && res.status == 'error') {
            this._event('loading', false)
            this._event('alert', { text: res.message })
            this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
                <p style="text-align: center;font-size: 1.5em;color: red;">${res.message}</p>          
                </div>`;

            if (res.reload && res.reload == 1) {
              this.$store.dispatch('dynamic/get', {
                page: this.page,
                key: 'OcrMatch',
              })
              setTimeout(() => {
                this.fatabInvoiceCheck = false
              }, 1000);
            }
            return false
          }
          if (!res || !res.id) {
            this._event('loading', false)
            this._event('alert', { text: 'خطایی رخ داده !!!' })
            this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
                  <p style="text-align: center;font-size: 1.5em;color: red;">خطایی رخ داده !!! !!!</p>          
                  <p style="text-align: center;color: red;">لطفا با پشتیبانی تماس بگیرید</p>          
                  </div>`
            return false
          }
          var downedTu = 0;
          res.bijacs.forEach(item => {
            downedTu++
            if (item.container_size && item.container_size == "_40Feet") {
              downedTu++
            }
          })
          var totalTu = Math.ceil(res.amount / res.Tariff)
          const mandeTu = totalTu - downedTu
          if (mandeTu == 0) {
            this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
            <p style="text-align: center;font-size: 1.5em;color: red;">تعداد ثبت شده : ${downedTu} TU</p>          
            <p>تعداد مجاز برای این فاکتور عبور نموده اند</p>     
            </div>`

            return false
          }

          this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
          <p style="text-align: center;font-size: 1.5em;color: red;">تعداد ثبت شده : ${downedTu} TU</p>          
          <p style="text-align: center;font-size: 1.5em;color: green;">تعداد مجاز : ${totalTu} TU</p>     
          <p>سایز کانتینر را انتخاب نمایید</p>     
          </div>`

          this.findInvoice = res.id
          if (mandeTu >= 1) this.fatabInvoiceCheck20tu = true
          if (mandeTu >= 2) this.fatabInvoiceCheck40tu = true

        }).catch((data) => {
          console.log(data)
          this._event('loading', false)
        }).finally(() => {
          this._event('loading', false)

        })

    },
    async selectContainer(data) {
      console.log(data, this.findInvoice)
      this._event('loading')
      await this.$axios
        .$post('/findAftabInvoice/addbijac', {
          tu: data,
          id: this.findInvoice,
          selectedTruckBase: this.selectedTruckBase.id,
        })
        .then((res) => {
          console.log(res)
          if (res && res.status && res.status == 'error') {
            this._event('alert', { text: res.message })
            this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
                <p style="text-align: center;font-size: 1.5em;color: red;">${res.message}</p>          
                </div>`;
            setTimeout(() => {
              this.fatabInvoiceCheck = false
            }, 1000);
          }

          this.$store.dispatch('dynamic/get', {
            page: this.page,
            key: 'OcrMatch',
          })
          this._event('alert', { text: "درحال لود مجدد ..." })

          const DATA = res.DATA;
          var downedTu = 0;
          DATA.bijacs.forEach(item => {
            downedTu++
            if (item.container_size && item.container_size == "_40Feet") {
              downedTu++
            }
          })
          var totalTu = Math.ceil(DATA.amount / DATA.Tariff)
          this.empinvoicetext = `<div dir="rtl"style="text-align: center;">
          <p style="text-align: center;font-size: 1.5em;color: red;">تعداد ثبت شده : ${downedTu} TU</p>          
          <p style="text-align: center;font-size: 1.5em;color: green;">تعداد مجاز : ${totalTu} TU</p>     
          </div>`

        }).catch((data) => {
          console.log(data)
        }).finally(() => {
          this._event('loading', false)
          this.invoiceAftab = ''
          // this.fatabInvoiceCheck = false
          this.fatabInvoiceCheck20tu = false
          this.fatabInvoiceCheck40tu = false
          this.aftabError = false
          this.aftabErrorMessage = ''
          this.findInvoice = 0
          // this.empinvoicetext = ''
        })
    }


  },
}
</script>

<style>
#padding-low>.v-card__title {
  padding: 8px !important;
}
</style>
