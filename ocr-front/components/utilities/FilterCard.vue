<template>
  <div class="pa-1">
    <v-card class="d-flex py-2 pr-4 pl-8   elevent-0 rounded-lg" :color="data.boxColor" >
      <v-avatar dark :color="data.avatarColor" class="" size="64">
        <v-icon :color="`${data.iconColor} !important`">{{ data.icon }}</v-icon>
      </v-avatar>
      <div class="d-flex flex-column justify-center mr-4">
        <span class="font-weight-bold">{{
          data?.text?.toLocaleString('fa-IR')
        }}</span>
        <span>{{ data.title }}</span>
      </div>
    </v-card>
  </div>
</template>

<script>
export default {
  name: 'FilterCard',
  props: {
    data: { default: null },
  },
  data: () => ({}),

  created() {},
}
</script>
