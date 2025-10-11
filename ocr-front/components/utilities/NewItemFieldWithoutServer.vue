<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        style="height: unset"
        class="pl-1"
        v-bind="attrs"
        text
        color="primary"
        dark
        v-on="on"
        @click.prevent="openDialog"
      >
        <v-icon>fal fa-plus</v-icon>
      </v-btn>
    </template>
    <span>{{ $attrs.title }}</span>
  </v-tooltip>
</template>

<script>
export default {
  methods: {
    openDialog() {
      this._event("newItem", {
        model: this.$attrs.sendModel,
        field: this.$attrs.sendField,
        title: this.$attrs.title,
        server: false,
      });
    },
  },
};
</script>
