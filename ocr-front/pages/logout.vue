<template>
<div></div>
</template>

<script>
export default {
  middleware: ['auth'],
  beforeCreate() {
    localStorage.removeItem('place');
    this.$auth.logout();
  }
}
</script>
