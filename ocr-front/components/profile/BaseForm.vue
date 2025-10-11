<template>
  <field-set :label="label" class="mt-5 pb-5" style="position: relative">
    <div style="position: absolute; left: 20px; bottom: 5px">
      <v-btn small elevation="0" color="success" @click="save">
        <v-icon small left>fal fa-check-circle</v-icon>
        <span>ذخیره</span>
      </v-btn>
      <v-btn small elevation="0" color="error" @click="remove">
        <v-icon small left>fal fa-times</v-icon>
        <span>حذف</span>
      </v-btn>
    </div>
    <DynamicForm
      ref="dynamicForm"
      v-model="form"
      :edit-item="editItem"
      :fields="fields"
    />
  </field-set>
</template>

<script>
import { DynamicForm, FieldSet } from "majra";

export default {
  components: { DynamicForm, FieldSet },

  props: {
    fn: {},
    editItem: { default: () => ({}) },
    fields: {},
    label: {},
  },

  data() {
    return {
      form: {},
    };
  },
};
</script>
