<template>
  <v-app>
    <div id="main">
      <div class="fof">
        <h1 style="direction: rtl">صفحه مورد نظر پیدا نشد</h1>
        <h2>
          <v-btn text to="/logout" class="mt-6">
            <b>بازگشت به صفحه اصلی</b>
            <v-icon>mdi-subdirectory-arrow-left</v-icon>
          </v-btn>
        </h2>
      </div>
    </div>
  </v-app>
</template>

<script>
export default {};
</script>

<style scoped>
* {
  transition: all 0.6s;
}

html {
  height: 100%;
}

body {
  font-family: "IRANSans" !important;
  color: #888;
  margin: 0;
}

#main {
  display: table;
  width: 100%;
  height: 100vh;
  text-align: center;
}

.fof {
  font-family: "IRANSans" !important;
  display: table-cell;
  vertical-align: middle;
}

.fof h1 {
  font-family: "IRANSans" !important;
  font-size: 50px;
  display: inline-block;
  padding-right: 12px;
  animation: type 0.5s alternate infinite;
}

@keyframes type {
  from {
    box-shadow: inset -3px 0px 0px #888;
  }
  to {
    box-shadow: inset -3px 0px 0px transparent;
  }
}
</style>
