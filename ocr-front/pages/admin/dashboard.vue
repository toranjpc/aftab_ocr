<template>
  <div>
    <h2>داشبورد</h2>
    <div class="d-flex flex-wrap justify-space-between mb-5">
      <div class="col-12 col-md-6">
        <v-card
          elevation="0"
          outlined
          class="px-4 rep-1"
          width="100%"
          height="100%"
        >
          <v-card-title class="font-weight-bold pr-2 pt-6">
            ورود و خروج به پارکینگ
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
      </div>

      <div class="col-12 col-md-6">
        <v-card
          elevation="0"
          outlined
          class="px-4 rep-1"
          width="100%"
          height="100%"
        >
          <v-card-title class="font-weight-bold pr-2 pt-6">
            ماشین‌های موجود در پارکینک
          </v-card-title>
          <div class="d-flex rep-2">
            <v-card
              class="d-flex py-7 pa-4 elevent-0 ma-2"
              color="#f4f7fa"
              outlined
            >
              <v-avatar dark color="#ede9fe" class="" size="64">
                <v-icon color="#8f62f6 !important">fal fa-pallet-boxes</v-icon>
              </v-avatar>
              <div class="d-flex flex-column justify-center mr-4">
                <span class="font-weight-bold">55</span>
                <span>فله</span>
              </div>
            </v-card>
            <v-card
              class="d-flex py-7 pa-4 elevent-0 ma-2"
              color="#f4f7fa"
              outlined
            >
              <v-avatar dark color="#fef3c7" class="" size="64">
                <v-icon color="#f59e0b !important"
                  >fal fa-truck-container-empty</v-icon
                >
              </v-avatar>
              <div class="d-flex flex-column justify-center mr-4">
                <span class="font-weight-bold">10</span>
                <span>کانتینر</span>
              </div>
            </v-card>
          </div>
          <div class="d-flex rep-2">
            <v-card
              class="d-flex py-7 pa-4 elevent-0 ma-2"
              color="#f4f7fa"
              outlined
            >
              <v-avatar dark color="#e0e7ff" class="" size="64">
                <v-icon color="#6366f1 !important">fal fa-truck</v-icon>
              </v-avatar>
              <div class="d-flex flex-column justify-center mr-4">
                <span class="font-weight-bold">34</span>
                <span>20 فوت</span>
              </div>
            </v-card>
            <v-card
              class="d-flex py-7 pa-4 elevent-0 ma-2"
              color="#f4f7fa"
              outlined
            >
              <v-avatar dark color="#dcfce7" class="" size="64">
                <v-icon color="#22c55e !important">fal fa-truck-moving</v-icon>
              </v-avatar>
              <div class="d-flex flex-column justify-center mr-4">
                <span class="font-weight-bold">99</span>
                <span>40 فوت</span>
              </div>
            </v-card>
          </div>
        </v-card>
      </div>

      <div class="col-12 col-md-4">
        <v-card
          elevation="0"
          outlined
          class="px-4 rep-1 py-5 pt-0"
          width="100%"
          height="100%"
        >
          <v-card-title
            class="d-flex justify-space-between font-weight-bold pr-2 pt-6 mb-10"
          >
            <span>آخرین ترددها</span>

            <span>
              <v-btn outlined color="blueLogo" small>مشاهده بیشتر</v-btn>
            </span>
          </v-card-title>
          <div class="d-flex rep-3">
            <v-card
              width="100%"
              class="d-flex elevent-0 my-2 justify-space-between pa-4 zoom-in"
            >
              <div class="d-flex mb-2">
                <v-avatar size="45">
                  <v-img src="/img/truck.png"></v-img>
                </v-avatar>
                <div class="d-flex flex-column justify-center mr-4">
                  <span class="text-subtitle-1 font-weight-bold">
                    123 س 44</span
                  >
                  <span class="text-body-1">فله</span>
                </div>
              </div>
              <div class="d-flex justify-center align-center">
                <v-chip
                  small
                  dark
                  label
                  class="my-auto"
                  color="yellowLogo blueLogo--text font-weight-bold"
                  >درب غربی</v-chip
                >
              </div>
            </v-card>
          </div>
          <div class="d-flex rep-3">
            <v-card
              width="100%"
              class="d-flex elevent-0 my-2 justify-space-between pa-4 zoom-in"
            >
              <div class="d-flex mb-2">
                <v-avatar size="45">
                  <v-img src="/img/truck.png"></v-img>
                </v-avatar>
                <div class="d-flex flex-column justify-center mr-4">
                  <span class="text-subtitle-1 font-weight-bold">
                    123 س 44</span
                  >
                  <span class="text-body-1">فله</span>
                </div>
              </div>
              <div class="d-flex justify-center align-center">
                <v-chip
                  small
                  dark
                  label
                  class="my-auto"
                  color="yellowLogo blueLogo--text font-weight-bold"
                  >درب غربی</v-chip
                >
              </div>
            </v-card>
          </div>
          <div class="d-flex rep-3">
            <v-card
              width="100%"
              class="d-flex elevent-0 my-2 justify-space-between pa-4 zoom-in"
            >
              <div class="d-flex mb-2">
                <v-avatar size="45">
                  <v-img src="/img/truck.png"></v-img>
                </v-avatar>
                <div class="d-flex flex-column justify-center mr-4">
                  <span class="text-subtitle-1 font-weight-bold">
                    123 س 44</span
                  >
                  <span class="text-body-1">فله</span>
                </div>
              </div>
              <div class="d-flex justify-center align-center">
                <v-chip
                  small
                  dark
                  label
                  class="my-auto"
                  color="yellowLogo blueLogo--text font-weight-bold"
                  >درب غربی</v-chip
                >
              </div>
            </v-card>
          </div>
        </v-card>
      </div>

      <div class="col-12 col-md-4">
        <v-card
          elevation="0"
          outlined
          color="blueLogo"
          width="100%"
          height="100%"
          class="px-4 rep-1 pb-5 justify-center align-center d-flex"
        >
          <div class="">
            <h6 class="text-h6 font-weight-black white--text text-center">
              گزارش گیری
            </h6>
            <p class="text-center white--text">
              برای دریافت گزارش کامل امروز روی لینک زیر کلیک کنید
            </p>
            <v-btn class="ma-auto d-block" text color="white"> دریافت </v-btn>
          </div>
        </v-card>
      </div>

      <div class="col-12 col-md-4">
        <v-card
          elevation="0"
          outlined
          width="100%"
          height="100%"
          class="w-100 px-4 rep-1 py-5 pt-0 d-flex flex-column"
        >
          <v-card-title class="font-weight-bold pr-2 pt-6">
            ماشین های بدون مجوز خروج
          </v-card-title>
          <div id="chart2">
            <apexchart
              type="radialBar"
              height="350"
              :options="chartOptions2"
              :series="series2"
            ></apexchart>
          </div>
        </v-card>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  layout: 'dashboard',

  data() {
    return {
      series: [
        {
          name: 'ورودی پارکینگ',
          data: [31, 40, 28, 51, 42, 109, 100],
        },
        {
          name: 'خروجی پارکینگ',
          data: [11, 32, 45, 32, 34, 52, 41],
        },
      ],
      chartOptions: {
        colors: ['#ffcc29', '#2957a4'],
        chart: {
          toolbar: {
            show: false,
            tools: {
              download: false,
            },
          },

          width: '100%',
          type: 'area',
          fontFamily: 'iransans',
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          curve: 'smooth',
        },
        xaxis: {
          tooltip: {
            enabled: false,
          },
          tickPlacement: 'between',
          type: 'datetime',
          categories: [
            '2018-09-19T00:00:00.000Z',
            '2018-09-19T01:30:00.000Z',
            '2018-09-19T02:30:00.000Z',
            '2018-09-19T03:30:00.000Z',
            '2018-09-19T04:30:00.000Z',
            '2018-09-19T05:30:00.000Z',
            '2018-09-19T06:30:00.000Z',
          ],
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm',
          },
        },
      },

      //دومی

      series2: [44, 55, 67, 83],
      chartOptions2: {
        colors: ['#ffcc29', '#2957a4', '#fe5f76', '#25e6a4'],
        chart: {
          width: '100%',
          fontFamily: 'iransans',
          height: 350,
          type: 'radialBar',
        },
        plotOptions: {
          radialBar: {
            dataLabels: {
              name: {
                fontSize: '22px',
              },
              value: {
                fontSize: '16px',
              },
              total: {
                show: true,
                label: 'همه',
                formatter: function (w) {
                  // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                  return 249
                },
              },
            },
          },
        },
        labels: ['فله', 'کانتینر', '20 فوت', '40 فوت'],
      },
    }
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
.apexcharts-yaxis-texts-g {
  transform: translate(-8px, 0);
}
.apexcharts-tooltip-marker {
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
