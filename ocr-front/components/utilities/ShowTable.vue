<template>
  <table class="table col-12">
    <thead>
      <tr>
        <td style="width: 1%"></td>
        <td>نام درس</td>
        <td>ساعت آموزش</td>
      </tr>
    </thead>
    <tbody>
      <template v-for="(items, key) in grouped">
        <tr v-for="(classRoom, index) in items" x>
          <td v-if="index === 0" :rowspan="items.length" class="text-center">
            {{ key == "null" ? "تخصصی" : key }}
          </td>
          <td>{{ classRoom.name }}</td>
          <td>{{ classRoom.number_of_sessions }}</td>
        </tr>
      </template>
    </tbody>
  </table>
</template>

<script>
export default {
  props: ["getFiles", "openImage", "value", "getValue"],

  computed: {
    grouped() {
      const out = {};
      for (const item of this.value.value) {
        if (!out[item.category]) {
          out[item.category] = [];
        }
        out[item.category].push(item);
      }

      return out;
    },
  },
};
</script>

<style>
td {
  padding: 5px;
}
</style>
