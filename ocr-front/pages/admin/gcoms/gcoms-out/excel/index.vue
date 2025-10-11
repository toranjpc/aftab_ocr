<template>
  <!-- <Financial @openBulkDialog="handleEventBulk" /> -->
  <span>
    <v-dialog v-model="dialog" max-width="700">
      <v-card id="dialog">
        <v-card-title>
          <span>گزارش درآمد</span>
          <v-spacer />
          <v-btn color="error" icon @click="dialog = false">
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="mt-4">
          <v-btn class="px-6" block large color="success" @click="exportExcel(1)">
            اکسل درامد امروز (تا به این لحظه)
          </v-btn>
          <h2 class="mt-6 mb-3 mr-4 font-weight-bold">تاریخ مشخص</h2>
          <div class="    d-flex    align-center">
            <DynamicForm ref="dynamicForm" v-model="form2" :fields="fields2" :edit-item="editItem2" />
            <v-btn outlined class="px-6" color="success" @click="exportExcel(2)">
              خروجی
            </v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
    <DynamicTemplate />
  </span>

</template>

<script>
import { DynamicTemplate, DynamicForm } from 'majra'
import fields from './fields'
import { getPermissions } from '~/helpers/helpers'
// import Financial from '~/components/gcoms/Financial.vue'

export default {
  components: { DynamicTemplate, DynamicForm },

  layout: 'dashboard',

  data() {
    return {
      dialog: false,
      fields2: [{
        title: 'تاریخ شروع ',
        field: 'startDate',
        type: 'date',
        props: {
          format: 'YYYY-MM-DD HH:mm:ss',
          type: 'datetime',
        },
        default: '',
        isHeader: false,
        col: { md: 6 },
      },
      {
        title: 'تاریخ پایان ',
        field: 'endDate',
        type: 'date',
        props: {
          format: 'YYYY-MM-DD HH:mm:ss',
          type: 'datetime',
        },
        default: '',
        isHeader: false,
        col: { md: 6 },
      },],
      form2: {},
      editItem2: {},

    }
  },
  beforeCreate() {
    const hiddenActions = getPermissions.call(this)

    this.$majra.init({
      hiddenActions,
      mainRoute: { route: '/gcoms-file?bank_maqsad=out', key: 'GcomsFile' },
      relations: [],
      fields: fields(this),
    })
  },

  methods: {
    async exportExcel(v) {
      if (v === 2) {
        if (this.form2.endDate === undefined || this.form2.startDate === undefined)
          return 0
      }

      this._event('loading')
      try {
        const response = await this.$axios.post('/export/gcoms', {
          time: this.form2,
        }, {
          responseType: 'blob' // این گزینه باعث می‌شود که پاسخ به صورت باینری باشد
        });

        // ایجاد یک URL برای Blob
        const url = window.URL.createObjectURL(new Blob([response.data]));

        // ایجاد یک عنصر لینک و تنظیم خصوصیات آن
        const link = document.createElement('a');
        link.href = url;

        // ایجاد یک نام فایل با استفاده از تاریخ امروز
        let fileName
        if (this.form2.endDate !== undefined)
          fileName = `gcoms-invoice-${new Date(this.form2.endDate).toLocaleDateString('fa-IR')}---${new Date(this.form2.startDate).toLocaleDateString('fa-IR')}.xlsx`;
        else
          fileName = `gcoms-invoice-${new Date().toLocaleDateString('fa-IR')}.xlsx`;

        link.setAttribute('download', fileName);

        // افزودن لینک به بدنه سند و کلیک خودکار روی آن
        document.body.appendChild(link);
        link.click();

        // حذف لینک پس از دانلود
        document.body.removeChild(link);
      } catch (error) {
        console.error('Error downloading the file', error);
      }
      this._event('loading', false)
      this.dialog = false
    },
  }
}
</script>
