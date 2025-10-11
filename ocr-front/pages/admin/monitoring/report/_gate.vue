<template>
  <div>
    <!-- آمار کلی بالای صفحه  -->
    <div class="d-flex justify-space-between mb-5">
      <v-card elevation="0" outlined width="99%" class="px-4 rep-1">
        <v-card-title class="font-weight-bold pr-2 pt-6">
          <span v-html="titleText(picker, 1)"> </span>
        </v-card-title>
        <div class="d-flex rep-2">
          <!-- کارت های داخلی -->
          <FilterCard
            v-for="(item, index) in headerCardData"
            :key="index"
            :data="headerCardData[index]"
          />
        </div>
        <div class="d-flex rep-2"></div>
      </v-card>
    </div>
    <div class="d-flex justify-space-between ml-4 align-start">
      <!-- سایدبار -->
      <div
        style="width: 24%; position: sticky; top: 7px"
        class="d-flex justify-space-between mb-5 mt-1 flex-column"
      >
        <!-- جستوجو -->
        <v-card elevation="0" class="mb-3 pa-3">
          <v-card-title
            class="d-flex justify-space-between font-weight-bold pr-2 pt-0 mb-0"
          >
            <span>جستوجو</span>
          </v-card-title>
          <SearchVehicleNumber />
          <SearchContaner />
          <v-chip
            :text-color="vehicle ? 'yellowLogo' : ''"
            :color="vehicle ? 'blueLogo' : ''"
            @click="_event('SearchVehicleNumber', 'test')"
          >
            پلاک
          </v-chip>
          <v-chip
            :text-color="cotainerNumber ? 'yellowLogo' : ''"
            :color="cotainerNumber ? 'blueLogo' : ''"
            @click="_event('SearchContanerNumber', 'test')"
          >
            شماره کانتینر
          </v-chip>
        </v-card>
        <!-- زمان بندی -->
        <v-card elevation="0" outlined width="100%" class="mb-3 pa-3 pt-1">
          <v-card-title
            class="d-flex justify-space-between font-weight-bold pr-2 mb-0"
          >
            <span>زمان بندی نمایش</span>
          </v-card-title>
          <v-autocomplete
            v-model="rangeDate"
            label="انتخاب"
            outlined
            hide-details
            defalt=""
            item-value="value"
            item-text="text"
            :items="[
              { value: 'daily', text: 'روزانه' },
              { value: 'calender', text: 'انتخاب زمانبندی' },
              { value: 'timePicer', text: 'ساعتی' },
            ]"
            @change="rangeDateChenge($event)"
          ></v-autocomplete>
          <v-row
            v-show="rangeDate === 'calender'"
            justify="center"
            class="ma-0 mt-3"
          >
            <date-picker
              id="picker-mamad"
              v-model="picker"
              range
              clearable
              :min="startDay"
              format="YYYY-MM-DD"
              display-format="jMMMM jD"
              inline
              @change="pickerChange"
              auto-submit
            >
            </date-picker>
          </v-row>
          <v-row
            v-show="rangeDate === 'timePicer'"
            justify="center"
            class="ma-0 mt-3"
          >
            <div class="d-flex flex-column">
              <date-picker
                id="picker-mamad"
                v-model="timePicer"
                @change="timePicerChange($event)"
                clearable
                format="YYYY-MM-DD"
                display-format="jMMMM jD"
                inline
                auto-submit
              ></date-picker>

              <v-card-title v-html="titleText(picker, 3)" />
            </div>
            <v-dialog
              v-model="dialog"
              class="white"
              max-width="316"
              min-height="300"
            >
              <v-card id="dialog">
                <v-card-title v-html="titleText(picker, 3)" />
                <v-card-text
                  style="
                    min-height: 300px;
                    width: 100%;
                    justify-content: center;
                    align-items: center;
                    display: flex;
                    font-size: 36px;
                    text-align: center;
                    padding: 0;
                  "
                >
                  <date-picker
                    v-if="!startTime"
                    id="picker-mamad"
                    v-model="startTime"
                    inline
                    auto-submit
                    type="time"
                    @change="startTimeChange($event)"
                  />
                  <span v-else>
                    <span
                      class="font-weight-bold text-center"
                      :style="!showPicker ? '' : 'display: none;'"
                      ><span
                        >ساعت اولیه
                        <br />
                        <br />
                        <br />
                      </span>
                      {{ startTime }}</span
                    >
                    <date-picker
                      id="picker-mamad"
                      v-model="endTime"
                      @change="endTimeChange($event)"
                      inline
                      :min="startTime"
                      auto-submit
                      type="time"
                      :style="showPicker ? '' : 'display: none;'"
                    />
                  </span>
                </v-card-text>
              </v-card>
            </v-dialog>
          </v-row>
        </v-card>
        <!-- فیلترهای پیشرفته -->
        <v-card
          elevation="0"
          outlined
          width="100%"
          class="px-4 rep-1 py-5 pt-0"
        >
          <v-card-title
            class="d-flex justify-space-between font-weight-bold pr-2 pt-8 mb-0"
          >
            <span>فیلترهای بیشتر</span>
          </v-card-title>

          <div class="d-flex flex-column">
            <v-container fluid>
              <!-- نوع ماشین -->
              <span>
                <v-checkbox
                  v-model="filtersPlatType"
                  @change="callSetFilter"
                  hide-details
                  value="afghan"
                  :label="`افغانی (${getSafe(
                    counts,
                    'afghan',
                    0
                  ).toLocaleString('fa-IR')})`"
                ></v-checkbox>
                <v-checkbox
                  v-model="filtersPlatType"
                  @change="callSetFilter"
                  hide-details
                  value="iran"
                  :label="`تریلی ایرانی (${getSafe(
                    counts,
                    'iran',
                    0
                  ).toLocaleString('fa-IR')})`"
                ></v-checkbox>
                <v-checkbox
                  v-model="filtersPlatType"
                  @change="callSetFilter"
                  hide-details
                  value="europe"
                  :label="`اروپایی (${getSafe(
                    counts,
                    'europe',
                    0
                  ).toLocaleString('fa-IR')})`"
                ></v-checkbox>
              </span>
              <!-- نوع کانتینر -->
              <span>
                <v-checkbox
                  v-model="filterContanerType"
                  @change="callSetFilter"
                  value="fale"
                  hide-details
                  :label="`فله (${getSafe(counts, 'fale', 0).toLocaleString(
                    'fa-IR'
                  )})`"
                ></v-checkbox>
                <v-checkbox
                  v-model="filterContanerType"
                  @change="callSetFilter"
                  hide-details
                  value="unknown"
                  :label="`نامشخص (${getSafe(
                    counts,
                    'unknown',
                    0
                  ).toLocaleString('fa-IR')})`"
                ></v-checkbox>
                <v-checkbox
                  v-model="filterContanerType"
                  @change="callSetFilter"
                  hide-details
                  value="2"
                  :label="`20 فوت (${getSafe(counts, '20f', 0).toLocaleString(
                    'fa-IR'
                  )})`"
                ></v-checkbox>
                <v-checkbox
                  v-model="filterContanerType"
                  @change="callSetFilter"
                  hide-details
                  value="4"
                  :label="`40 فوت  (${getSafe(counts, '40f', 0).toLocaleString(
                    'fa-IR'
                  )})`"
                ></v-checkbox>
              </span>
            </v-container>
          </div>
        </v-card>
      </div>
      <!-- قسمت وسطی -->
      <div class="d-flex flex-column" style="width: 75%">
        <v-card elevation="0" outlined class="px-4 mb-3">
          <v-card-title
            v-html="titleText(picker, 2)"
            class="font-weight-bold pr-2 pt-6"
          >
          </v-card-title>
          <v-card-text>
            <div id="chart">
              <apexchart
                type="area"
                height="263"
                :options="chartOptions"
                :series="series"
              ></apexchart>
            </div>
          </v-card-text>
        </v-card>
        <DynamicTemplate class="test" />
      </div>
    </div>
  </div>
</template>

<script>
import { DynamicTemplate } from 'majra'
import { get as getSafe, clone } from 'lodash'
import { mapGetters, mapActions } from 'vuex'
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'
import fields from '../../ocr/fields'
import SearchVehicleNumber from '~/components/station/SearchVehicleNumber.vue'
import SearchContaner from '~/components/station/SearchContaner.vue'
import FilterCard from '~/components/utilities/FilterCard.vue'

export default {
  name: 'ReportLog',

  components: {
    DynamicTemplate,
    SearchVehicleNumber,
    SearchContaner,
    datePicker: VuePersianDatetimePicker,
    FilterCard,
  },

  layout: 'dashboard',

  data() {
    return {
      startTime: null,
      endTime: null,
      showPicker: false,
      timePicer: null,
      dialog: false,
      filtersPlatType: [],
      filterContanerType: [],
      vehicle: null,
      cotainerNumber: null,
      counts: null,
      rangeDate: 'daily',
      picker: null,
      startDay: null,
      series: [
        {
          name: 'خروجی پارکینگ',
          data: [],
        },
      ],
      chartOptions: this.getChartOptions(),
    }
  },

  created() {
    this.$majra.init({
      hiddenActions: ['delete', 'show', 'edit', 'create'],
      mainRoute: {
        route: `/truck-log?id=1`, // چون فقط باید باشه سبک بگیرم چطوره ؟
        key: 'TruckLog',
      },
      fields: fields(this),
    })

    // خوب اینجا می گه برو دیتا رو بگیر
    setTimeout(() => {
      this.setfilter()
    }, 1000)

    // برای سرچ هاست
    this._listen('SearchVehicle', (v) => {
      this.vehicle = v
      this.callSetFilter()
    })
    this._listen('SearchContaner', (v) => {
      this.cotainerNumber = v
      this.callSetFilter()
    })
  },

  computed: {
    ...mapGetters({
      flatFields: 'dynamic/flatFields',
      headers: 'dynamic/allHeaders',
    }),

    getsumContaner() {
      return this.counts
        ? this.counts['20f'] + this.counts['40f'] + this.counts.unknown
        : 0
    },

    total() {
      return this.counts
        ? this.counts.iran + this.counts.afghan + this.counts.europe
        : 0
    },

    headerCardData() {
      return [
        {
          icon: 'fal fa-pallet-boxes',
          title: 'کل ترددها',
          avatarColor: '#ede9fe',
          iconColor: '#8f62f6',
          boxColor: '#f4f7fa',
          text: this.total,
        },
        {
          icon: 'fal fa-container-storage',
          title: 'داری کانتینر',
          avatarColor: '#fef3c7',
          iconColor: '#f59e0b',
          boxColor: '#f4f7fa',
          text: this.getsumContaner,
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-truck',
          title: '20 فوت',
          avatarColor: '#e0e7ff',
          iconColor: '#6366f1',
          text: this.getSafe(this.counts, '20f', 0),
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-truck-moving',
          title: '40 فوت',
          avatarColor: '#dcfce7',
          iconColor: '#22c55e',
          text: this.getSafe(this.counts, '40f', 0),
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-font-awesome',
          title: 'تریلی ایرانی',
          avatarColor: '#a5c4f885',
          iconColor: '#2957a4',
          text: this.getSafe(this.counts, 'iran', 0),
        },
      ]
    },
  },

  methods: {
    pickerChange() {
      if (this.picker.length === 1) this.startDay = this.picker[0]
      else this.callSetFilter()
    },
    titleText(picker, type) {
      if (this.startTime || this.endTime) {
        console.log(this.endTime, 'ssssssssssss', this.startTime)
        let x = type == 1 ? 'ترددهای' : type == 2 ? 'گزارش وضعیت' : ''

        return `
        ${x}
        ${new Date(this.timePicer).toLocaleString('fa-IR', {
          year: 'numeric',
          month: 'numeric',
          day: 'numeric',
        })}
        از ساعت
                <span class="mx-1 orange--text darken-2">
                  ${this.startTime}
                </span>
                تا ساعت
                <span class="mx-1 orange--text darken-2">
                  ${this.endTime ? this.endTime : ''}
                </span>
                <span  class="mx-1">${!this.endTime ? '-' : ''}</span>`
      }
      if (getSafe(picker, 'length', 0) == 0)
        return type == 1 ? 'ترددهای امروز' : 'گزارش وضعیت امروز'
      let text = type == 1 ? 'ترددها از تاریخ' : 'گزارش وضعیت از تاریخ'
      return `${text}
               ${new Date(getSafe(picker, [0], 0)).toLocaleString('fa-IR', {
                 year: 'numeric',
                 month: 'numeric',
                 day: 'numeric',
               })}
            تا
              ${new Date(getSafe(picker, [1], 0)).toLocaleString('fa-IR', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
              })}`
    },

    getChartOptions() {
      return {
        colors: ['#ffcc29', '#2957a4'],
        chart: {
          toolbar: { show: false, tools: { download: false } },
          width: '100%',
          type: 'area',
          fontFamily: 'iransans',
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: {
          tooltip: { enabled: false },
          tickPlacement: 'between',
          type: 'category',
        },
        tooltip: {},
      }
    },

    timePicerChange() {
      this.endTime = null
      this.startTime = null
      this.showPicker = false
      this.dialog = true
    },
    endTimeChange($event) {
      const end = new Date($event).toLocaleTimeString('en-GB')
      if (end) {
        this.dialog = false
        const time = [
          `${this.timePicer}  ${this.startTime}`,
          `${this.timePicer}  ${end}`,
        ]
        this.setfilter(
          this.filtersPlatType,
          this.filterContanerType,
          this.vehicle,
          this.cotainerNumber,
          time
        )
      }
    },

    startTimeChange(newValue) {
      if (newValue != null) {
        setTimeout(() => {
          this.showPicker = true
        }, 1000)
      }
    },
    rangeDateChenge(newValue) {
      if (newValue === 'daily') {
        this.timePicer = null
        this.startTime = null
        this.endTime = null
        this.picker = null
        this.callSetFilter()
      }
    },
    getSafe,
    ...mapActions({
      getWithFilter: 'dynamic/getWithFilter',
    }),
    callSetFilter() {
      this.setfilter(
        this.filtersPlatType,
        this.filterContanerType,
        this.vehicle,
        this.cotainerNumber,
        this.picker
      )
    },

    async setfilter(
      filtersPlatType = ['iran', 'afghan', 'europe'],
      filterContanerType = [],
      vehicle = null,
      cotainerNumber = null,
      date = null
    ) {
      // تنظیمات اولیه برای نمودار و داده‌های فیلتر
      try {
        const res = await this.$axios.$post('/log/rip', {
          filtersPlatType,
          filterContanerType,
          vehicle,
          cotainerNumber,
          date,
          gateNumber: [1],
          gateNumberDefult: [1],
        })
        this.series[0].data = res.chart
        this.series = clone(this.series)
        this.counts = res.counts[0]
      } catch (error) {
        console.error('Error posting data:', error)
      }

      this.setInitialData(
        filtersPlatType,
        filterContanerType,
        date,
        cotainerNumber,
        vehicle
      )
      this.$store.commit('dynamic/setIsFiltering', true)
      this.getWithFilter()
    },

    setInitialData(
      filtersPlatType,
      filterContanerType,
      date,
      cotainerNumber,
      vehicle
    ) {
      filtersPlatType =
        filtersPlatType?.length === 0
          ? ['iran', 'afghan', 'europe']
          : filtersPlatType

      this.setFilterData('selects', 'gate_number', [1])

      this.setFilterData('order', 'log_time', 'DESC')
      const formattedDate = date || [this.formatDate(new Date())]
      this.setFilterData('dates', 'log_time', formattedDate)

      if (cotainerNumber !== 0 && cotainerNumber) {
        this.setFilterData('fields', 'container_show')
        this.setFilterData('search', cotainerNumber)
      } else if (cotainerNumber === 0) {
        this.setFilterData('fields', null)
        this.setFilterData('search', '')
      }

      if (filtersPlatType.length || filterContanerType.length) {
        this.setSelectsData(filtersPlatType, filterContanerType, vehicle)
      }
    },

    setFilterData(field, key, data) {
      if (field === 'order') {
        this.$store.commit('dynamic/setFilterData', {
          field,
          data: { key: key, value: data },
        })
      } else if (field === 'fields') {
        this.$store.commit('dynamic/setFilterData', {
          field,
          data: key ? [key] : [],
        })
      } else if (field === 'search') {
        this.$store.commit('dynamic/setFilterData', {
          field,
          data: key,
        })
      } else {
        this.$store.commit('dynamic/setFilterData', {
          field,
          data: { [key]: data },
        })
      }
    },

    setSelectsData(filtersPlatType, filterContanerType, vehicle) {
      const data = {
        ...(vehicle && { plate_num: [vehicle] }),
        ...(filtersPlatType.length && { plate_type: filtersPlatType }),
        ...(filterContanerType.length && {
          container_size: filterContanerType,
        }),
        gate_number: [1],
      }
      this.$store.commit('dynamic/setFilterData', { field: 'selects', data })
    },

    formatDate(date) {
      const options = { year: 'numeric', month: '2-digit', day: '2-digit' }
      return date
        .toLocaleDateString('en-GB', options)
        .split('/')
        .reverse()
        .join('/')
    },
  },
}
</script>

<style scoped>
.zoom-in {
  transition-property: transform, box-shadow;
  transition-duration: 0.3s;
  transition-timing-function: cubic-bezier(0, -0.58, 0.01, 0.04);
  cursor: pointer;
  box-shadow: 0 0 0 0 !important;
}
.zoom-in:hover {
  transform: scale(1.05);
  box-shadow: -1px 2px 25px -5px rgba(0, 0, 0, 0.10196),
    1px 7px 10px -6px rgba(0, 0, 0, 0.10196) !important;
}
.theme--light.v-sheet--outlined {
  border-color: rgb(226, 232, 240);
}
.v-main__wrap {
  display: block !important;
}
</style>
<style>
.test th[aria-label='اقدامات'] {
  display: none;
}
.test td:last-child {
  display: none;
}
.test .v-sheet .v-card__title {
  display: none;
}
.test .v-data-table-header {
  padding-top: 10px !important;
}
.apexcharts-yaxis-texts-g {
  transform: translate(-8px, 0);
}
.apexcharts-tooltip-marker {
  margin-right: 0 !important;
  margin-right: 0 !important;
  margin-left: 10px;
}
.apexcharts-tooltip-series-group {
  justify-content: space-between;
}
.rep-1 {
  width: 48%;
}
.rep-2 .v-card {
  width: 48%;
}
.apexcharts-canvas {
  display: flex;
  max-width: 100%;
}
</style>
<style>
#__layout #picker-mamad .vpd-header {
  background-color: var(--v-blueLogo-base) !important;
}
#__layout #picker-mamad .v-picker__body {
  min-height: fit-content !important;
}
#__layout #picker-mamad .v-date-picker-table {
  height: fit-content !important;
}
#__layout #picker-mamad .vpd-input-group {
  display: none;
}
</style>
