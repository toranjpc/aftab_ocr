<template>
  <div class="btn position-relative hover-show-img">
    <a :href="link || '/login'"><slot></slot></a>
    <img
      class="gerdali"
      style="width: 170px; position: absolute; top: -23px; left: -23px"
      src="/img/gerdali.png"
    />
  </div>
</template>

<script>
export default { props: { link: {} } }
</script>

<style scoped>
.gerdali {
  transition: all 1s;
  opacity: 0;
}
.hover-show-img:hover .gerdali {
  opacity: 1;
  transform: rotate(360deg);
}
.btn {
  position: relative;
  top: 0;
  left: 0;
  width: 125px;
  height: 125px;
  margin: 0;
  display: flex;
  justify-content: center;
  align-items: center;
}
.btn a {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(255, 255, 255, 0.05);
  box-shadow: 0 15px 15px rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 30000px;
  padding: 10px;
  letter-spacing: 1px;
  text-decoration: none;
  overflow: hidden;
  color: #fff;
  font-weight: 400px;
  z-index: 1;
  transition: 0.5s;
  backdrop-filter: blur(15px);
}
</style>
