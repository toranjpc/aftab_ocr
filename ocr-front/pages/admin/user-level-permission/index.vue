<template>
  <dynamic-template>
    <template v-slot:dialog="{ isEditing, editItem }">
      <EditUserLevelPermissionDialogForm :isEditing="isEditing" :item="editItem" />
    </template>
  </dynamic-template>
</template>

<script>
import { DynamicTemplate } from 'majra'
import { getPermissions } from '~/helpers/helpers';
const EditUserLevelPermissionDialogForm = () =>
  import("@/components/user/editUserLevelPermissionDialogForm");

export default {
  components: {
    EditUserLevelPermissionDialogForm,
    DynamicTemplate
  },

  layout: "dashboard",

  created() {
    const hiddenActions = getPermissions.call(this)

    this.$majra.init({
      hiddenActions,
      mainRoute: "/user-level-permission",
      hideActions: ["show"],
      fields: [
        {
          title: "عنوان",
          field: "name",
          rel: false,
          type: "text",
          isHeader: true,
          rules: "required"
        },
        {
          title: "دسترسی ها",
          field: "permission_do",
          rel: false,
          type: "text",
          rules: "required"
        }
      ]
    });
  }
};
</script>

<style scoped>
tbody tr:nth-of-type(odd) .ss {
  background: #f4f4f4;
  color: #f4f4f4;
}

tbody tr .ss {
  background: #fff;
  color: #fff;
}

tbody tr:hover .ss {
  background-color: #eee;
  color: #eee;
}
</style>
