<template>
  <div class="d-flex flex-row flex-wrap align-start">
    <div class="col-12">
      <CardTitle :title="titleText()">
        <div class="d-flex flex-row flex-wrap justify-center pa-3">
          <FilterCard
            v-for="(item, index) in headerCardData"
            :key="index"
            :data="headerCardData[index]"
          />
        </div>
      </CardTitle>
    </div>

    <div class="col-4" style="position: sticky; top: 7px; z-index: 2">
      <CardWidget title="جستوجو" class="mb-2">
        <template #actions>
          <v-btn class="mx-1" small icon color="success" @click="filter">
            <v-icon small>fal fa-check</v-icon>
          </v-btn>
          <v-btn
            class="mx-1"
            small
            icon
            color="danger"
            @click=";[(editItem = {}), filter()]"
          >
            <v-icon small>fal fa-times</v-icon>
          </v-btn>
        </template>
        <DynamicForm v-model="form" :fields="fields" :editItem="editItem" />
      </CardWidget>
    </div>

    <div class="col-8">
      <DynamicTemplate>
        <template #table="{ items }">
          <div v-for="item in items" class="px-2 pt-1 my-1">
            <v-card style="height: 100px; width: 100%">
              <v-card-text>
                {{ item.plate_number }}
                {{ item.container_code }}

                <div v-if="item?.bijacs?.length > 0" class="ml-2">
                  <v-btn
                    small
                    color="success"
                    dark
                    @click="_event('ccs.dialog', item)"
                  >
                    نمایش فاکتور
                  </v-btn>
                </div>
              </v-card-text>
            </v-card>
          </div>

          <div v-if="items && items?.length === 0">
            <h2 class="pa-5 pb-15 text-center">موردی یافت نشد!</h2>
          </div>
        </template>

        <template #extra>
          <FactorDialog />
        </template>
      </DynamicTemplate>
    </div>
  </div>
</template>

<script>
import qs from 'qs'
import fields from './fields'
import { get as getSafe } from 'lodash'
import reportFields from './reportFields'
import { mapGetters, mapActions } from 'vuex'
import { DynamicForm, DynamicTemplate } from 'majra'
import CardTitle from '~/components/widgets/CardTitle.vue'
import CardWidget from '~/components/widgets/CardWidget.vue'
import FilterCard from '~/components/utilities/FilterCard.vue'
import FactorDialog from '~/components/truckLog/FactorDialog.vue'

export default {
  name: 'ReportLog',

  components: {
    CardTitle,
    FilterCard,
    CardWidget,
    DynamicForm,
    FactorDialog,
    SearchContaner,
    DynamicTemplate,
    SearchVehicleNumber,
  },

  layout: 'dashboard',

  data() {
    return {
      dialog: false,
      filtersPlatType: [],
      filterContanerType: [],
      counts: null,
      fields: reportFields(this),
      form: {},
      editItem: {},
      series: [
        {
          name: 'خروجی پارکینگ',
          data: [],
        },
      ],
      chartOptions: [],
    }
  },

  created() {
    this.$majra.init({
      mainRoute: {
        route: `/ocr-log?_append=invoice&_with=bijacs&`,
        key: 'OcrLog',
      },
      fields: fields(this),
      options: {
        listType: 'card',
        cardColAttrs: {
          class: 'col-12',
        },
      },
    })
  },

  computed: {
    ...mapGetters({
      flatFields: 'dynamic/flatFields',
      headers: 'dynamic/allHeaders',
    }),

    headerCardData() {
      return [
        {
          icon: 'fal fa-pallet-boxes',
          title: 'کل ترددها',
          avatarColor: '#ede9fe',
          iconColor: '#8f62f6',
          boxColor: '#f4f7fa',
          text: 0,
        },
        {
          icon: 'fal fa-container-storage',
          title: 'کانتینری',
          avatarColor: '#fef3c7',
          iconColor: '#f59e0b',
          boxColor: '#f4f7fa',
          text: 0,
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-truck',
          title: '20 فوت',
          avatarColor: '#e0e7ff',
          iconColor: '#6366f1',
          text: 0,
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-truck-moving',
          title: '40 فوت',
          avatarColor: '#dcfce7',
          iconColor: '#22c55e',
          text: 0,
        },
        {
          icon: 'fal fa-container-storage',
          title: 'فله',
          avatarColor: '#fef3c7',
          iconColor: '#f59e0b',
          boxColor: '#f4f7fa',
          text: 0,
        },
        {
          boxColor: '#f4f7fa',
          icon: 'fal fa-font-awesome',
          title: 'مجموع بار خروجی',
          avatarColor: '#a5c4f885',
          iconColor: '#2957a4',
          text: 0,
        },
      ]
    },
  },

  methods: {
    getSafe,
    ...mapActions({
      getWithFilter: 'dynamic/getWithFilter',
    }),
    titleText() {
      return 'گزارش'
    },
    makeQuery() {
      let query = {}

      for (const field of this.fields) {
        if ('filterFormat' in field && !!getSafe(this.form, field.field)) {
          const q = field.filterFormat(this.form)

          query = { ...query, ...q }
        }
      }

      return qs.stringify(
        {
          filters: query,
        },
        {
          encodeValuesOnly: true, // prettify URL
        }
      )
    },
    filter() {
      const query = this.makeQuery()

      console.log(query)

      this.$store.dispatch('dynamic/midit', {
        relations: [
          {
            route: `/ocr-log?_append=invoice&_with=bijacs&${query}&disable_all=true`,
            key: 'OcrLog',
          },
        ],
      })
    },
  },
}
</script>
