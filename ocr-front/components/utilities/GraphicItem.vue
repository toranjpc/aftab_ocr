<template>
  <li>
    <div class="numberWrap">
      <div class="number fontColor1">1</div>
      <div class="coverWrap">
        <div class="numberCover"></div>
      </div>
    </div>
    <div class="content">
      <v-icon class="icon">fal fa-user</v-icon>
      <h2>Develop</h2>
      <p>Sagittis, audantium sem eveniet lacus pede porttitor aenean.</p>
    </div>
  </li>
</template>

<style scoped>
ul.infoGraphic li .numberWrap {
  position: absolute;
}

ul.infoGraphic li .number {
  font-family: "maven pro", sans-serif;
  font-size: 13em;
  font-weight: 900;
  width: 0.9em;
  text-align: center;
}

ul.infoGraphic li .coverWrap {
  transform: rotate(130deg);
  position: absolute;
  width: 18em;
  height: 15em;
  left: -3em;
  top: -1em;
}

ul.infoGraphic li .coverWrap .numberCover {
  position: absolute;
  background: var(--bg1);
  width: 18em;
  height: 6em;
  border-radius: 50% 50% 0 0;
  border-bottom: 3px solid #f5f8f7;
  transition: all 0.4s;
}

ul.infoGraphic li .coverWrap .numberCover::before {
  position: absolute;
  content: "";
  bottom: 5px;
  left: 4em;
  right: 4em;
  top: 5em;
  box-shadow: 0 0 30px 17px #48668577;
  border-radius: 100px/10px;
  z-index: -1;
}

ul.infoGraphic li .coverWrap .numberCover::after {
  position: absolute;
  bottom: 0;
  content: "";
  left: -10%;
  width: 120%;
  height: 150%;
  background: radial-gradient(at bottom, #48668533, transparent, transparent);
  z-index: 1;
}

ul.infoGraphic li:hover .coverWrap .numberCover {
  border-radius: 100%;
}

.icon {
  position: absolute;
  font-size: 2rem;
  text-align: center;
  top: -1.3em;
  left: 50%;
  transform: translatex(-50%);
}

ul.infoGraphic li .content {
  margin: 8em 3em 1em 7em;
  position: relative;
}

ul.infoGraphic li .content h2 {
  font-size: 1.7em;
  font-weight: 500;
  text-align: center;
  margin-bottom: 10px;
  text-transform: uppercase;
}

ul.infoGraphic li .content p {
  line-height: 1.5em;
}

li .number.fontColor1 {
  color: var(--blue);
}

li {
  position: relative;
  width: 100%;
  max-width: 25em;
  background: var(--bg1);
  border-radius: 0.5em;
  padding: 0.5em;
  z-index: 1;
  transition: all 0.2s;
  cursor: pointer;
}
</style>
