<template>
  <field-set style="position: relative" label="دسترسی">
    <v-btn
      style="position: absolute; top: -20px"
      :style="$vuetify.rtl ? 'left: 5px;' : 'right: 20px;'"
      color="success"
      x-small
      elevation="0"
      @click="addDialog = true"
    >
      <v-icon size="10">fal fa-plus</v-icon>
    </v-btn>

    <div class="d-block">
      <v-chip
        v-for="permission in getPermissions"
        :key="permission.name"
        class="ma-2"
        close
        color="orange"
        label
        outlined
        @click:close="
          updateField(value.filter((per) => per.id !== permission.id))
        "
      >
        {{ permission.name }}
      </v-chip>
      <h3 v-if="!value.length" class="text-center">
        شما دسترسی اضافه کردن ندارید
      </h3>
    </div>

    <v-overlay v-model="addDialog"></v-overlay>

    <v-dialog
      v-model="addDialog"
      :width="$vuetify.breakpoint.lgAndUp ? (1 / 2) * 100 + '%' : '100%'"
      class="fill-height"
      transition="dialog-bottom-transition"
      :fullscreen="$vuetify.breakpoint.mdAndDown"
      scrollable
    >
      <v-card color="transparent">
        <v-card-title v-if="$vuetify.breakpoint.mdAndDown" class="pb-0">
          <v-btn
            tile
            class="mx-auto rounded-t-circle elevation-0"
            color="white"
            @click="addDialog = false"
          >
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-title v-else class="secondary py-1">
          <v-spacer />
          <v-btn class="mx-auto" icon color="white" @click="addDialog = false">
            <v-icon>fal fa-times</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text
          class="white"
          :class="$vuetify.breakpoint.mdAndDown ? 'rounded-t-lg' : ''"
        >
          <v-treeview
            v-model="value"
            :items="permissions"
            selected-color="indigo"
            open-on-click
            selectable
            return-object
            expand-icon="mdi-chevron-down"
            @input="updateField([...value])"
          />
        </v-card-text>
        <v-card-actions class="secondary">
          <v-spacer></v-spacer>
          <v-btn small color="white" text @click="addDialog = false">تایید</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </field-set>
</template>

<script>
import { AbstractField, FieldSet } from 'majra'
import permissions from '@/helpers/permissions'

export default {
  name: 'RoleAddPermission',

  components: { FieldSet },

  extends: AbstractField,

  data() {
    return {
      addDialog: false,
      permissions: permissions(this),
    }
  },

  computed: {
    getPermissions() {
      if (!Array.isArray(this.value) || this.value.length < 1) return []
      // if(this.value[0].name == undefined)
      return this.value
    },
  },
}
</script>
