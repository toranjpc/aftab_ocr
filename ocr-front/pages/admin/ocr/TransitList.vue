<!--
<template>
  <div>
    <v-data-table :headers="headers" :items="items" :loading="loading" class="elevation-1" disable-pagination
      hide-default-footer dense>
      <template v-slot:item="props">
        <tr>
          <td>{{ props.index + 1 }}</td>
          <td v-for="field in fields" :key="field.field" v-html="renderField(field, props.item)"></td>
          <td>
            <v-btn v-if="props.item.bijacs[0]?.ocr_matches_count > 1" small class="px-1"
              :color="getBtnColor(props.item)">
              <v-icon :color="getIconColor(props.item)">mdi-repeat</v-icon>
            </v-btn>
          </td>
          <td>
            <v-btn small :color="renderBTN(props.item).color" dark @click="_event('ccs.dialog', props.item)">
              {{ renderBTN(props.item).text }}
            </v-btn>
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
  <template>
    <FactorDialog />
  </template>
</template>
-->

<template>
  <div> <!-- این عنصر ریشه واحد است -->
    <div> <!-- این div شامل v-data-table است -->
      <v-data-table :headers="headers" :items="items" :loading="loading" class="elevation-1" disable-pagination
        hide-default-footer dense>
        <template v-slot:item="props">
          <tr>
            <td>{{ props.index + 1 }}</td>
            <td v-for="field in fields" :key="field.field" v-html="renderField(field, props.item)"></td>
            <td>
              <v-btn v-if="props.item.bijacs[0]?.ocr_matches_count > 1" small class="px-1"
                :color="getBtnColor(props.item)">
                <v-icon :color="getIconColor(props.item)">mdi-repeat</v-icon>
              </v-btn>
            </td>
            <td>
              <!-- {{ props.item.id }} -->
              <v-btn small :color="renderBTN(props.item).color" dark @click="_event('ccs.dialog', props.item)">
                {{ renderBTN(props.item).text }}
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>
    </div>

    <FactorDialog /> <!-- FactorDialog نیز در همین عنصر ریشه قرار می‌گیرد -->

  </div>
</template>


<script>
import { get as getSafe } from 'lodash'
import FactorDialog from '~/components/truckLog/FactorDialog.vue'

export default {
  components: {
    FactorDialog
  },

  props: {
    item: { type: Object, required: true },
    fields: { type: Array, required: true },
  },

  data() {
    return {
      items: [],
      loading: false,
      colors: [
        { btn: 'yellow lighten-1', icon: 'yellow darken-1' },
        { btn: 'red lighten-3', icon: 'red darken-2' },
        { btn: 'blue lighten-3', icon: 'blue darken-2' },
        { btn: 'green lighten-3', icon: 'green darken-2' },
        { btn: 'purple lighten-3', icon: 'purple darken-2' },
        { btn: 'orange lighten-3', icon: 'orange darken-2' },
        { btn: 'teal lighten-3', icon: 'teal darken-2' },
        { btn: 'pink lighten-3', icon: 'pink darken-2' },
        { btn: 'indigo lighten-3', icon: 'indigo darken-2' },
        { btn: 'cyan lighten-3', icon: 'cyan darken-2' },
        { btn: 'amber lighten-3', icon: 'amber darken-2' }
      ]
    }
  },

  computed: {
    headers() {
      return [
        { text: '#', value: 'rowNumber' },
        ...this.fields.map(f => ({
          text: f.title,
          value: f.field,
          align: 'start'
        })),
        { text: '', value: 'repeat' },
        { text: 'وضعیت', value: 'match_status' },
      ]
    },

    repetitiveColorMap() {
      const counts = {}
      this.items.forEach(item => {
        const id = item.bijacs?.[0]?.id
        if (id) counts[id] = (counts[id] || 0) + 1
      })

      const duplicates = Object.keys(counts).filter(id => counts[id] > 1)

      const map = {}
      duplicates.forEach((id, index) => {
        const colorIndex = index % this.colors.length
        map[id] = this.colors[colorIndex]
      })

      return map
    }
  },

  mounted() {
    this.fetchData()
  },

  methods: {
    async fetchData() {
      this.loading = true
      try {
        const res = await this.$axios.get(`/ocr-match/${this.item.id}/items`)
        this.items = res.data?.data || []
      } catch (err) {
        console.error('خطا در دریافت داده‌ها', err)
      } finally {
        this.loading = false
      }
    },

    renderField(field, item) {
      try {
        if (typeof field.inList === 'function') {
          return field.inList(item[field.field], item)
        }
        return item[field.field] ?? '-'
      } catch (e) {
        return '-'
      }
    },

    renderBTN(item, ifFalse = false) {
      var status = item.match_status
      if (!status) {
        return {
          text: 'در حال جستجو فاکتور موردی',
          color: 'grey',
        }
      }

      let is_single_carry = ''
      var req = ''

      if (item && item.bijacs && item.bijacs.length) {
        for (let c = 0; c < item.bijacs.length; c++) {
          if (item.bijacs[c].is_single_carry == 1) {
            is_single_carry = ' (حمل یکسره) '

            break
          }
        }
      }

      if (status.includes('_req')) {
        req = ' - موردی'
        status = status.replace('_req', '')
      }
      if (status.includes('_Creq')) {
        req = ' - تایید دستی بیجک'
        status = status.replace('_Creq', '')
      }
      req = req + is_single_carry

      let list = {
        // bad_match_nok: ['دو فاکتور متفاوت', 'purple'],
        gcoms_ok: ['فاکتور' + req, 'cyan'],
        gcoms_nok: ['بدون فاکتور' + req, 'red'],
        ccs_ok: ['فاکتور' + req, 'green darken-4'],
        ccs_nok: ['بدون فاکتور' + req, 'red'],
        container_without_bijac: ['بدون بیجک' + req, 'orange'],
        plate_without_bijac: ['بدون بیجک', 'orange'],
        container_ccs_ok: ['فاکتور (کانتینر)' + req, 'green'],
        container_ccs_nok: ['بدون فاکتور' + req, 'red'],
        plate_ccs_ok: ['فاکتور (پلاک)' + req, 'green'],
        plate_ccs_nok: ['بدون فاکتور' + req, 'red'],
      }
      if (ifFalse) {
        list['container_without_bijac'] = ['بیجک تأیید شده', 'success']
        list['plate_without_bijac'] = ['بیجک تأیید شده', 'success']
      }

      return {
        text: getSafe(list, status + '[0]', status),
        color: getSafe(list, status + '[1]', 'grey'),
      }
    },

    getBtnColor(item) {
      const id = item.bijacs?.[0]?.id
      if (!id) return ''
      return this.repetitiveColorMap[id]?.btn || ''
    },

    getIconColor(item) {
      const id = item.bijacs?.[0]?.id
      if (!id) return ''
      return this.repetitiveColorMap[id]?.icon || ''
    }
  }
}
</script>
