<template>
  <v-list-group
    v-show="permission"
    :sub-group="sub"
    :value="openSid(top)"
    :class="{ ltrMamad: sub }"
    exact
    to="/"
    link
  >
    <template v-slot:activator>
      <v-list-item-action v-if="!sub">
        <i :class="top.icon + ' test'"></i>
      </v-list-item-action>
      <v-list-item-content
        :ref="top.text"
        class="ssssssssss"
        :class="isEl ? 'scroll-mamad' : ''"
        @click="renderChild"
      >
        <v-list-item-title>{{ top.text }} </v-list-item-title>
      </v-list-item-content>
      <v-list-item-action v-if="sub && top.icon">
        <i :class="top.icon"></i>
      </v-list-item-action>
    </template>

    <template v-for="child in top.children">
      <v-list-item
        v-if="!child.isGroup && child.status !== 'dontShow'"
        :key="child.text"
        v-permission:any="standardPermission(child.to)"
        :to="child.to"
        :link="child.link"
        exact
        class="mr-2"
      >
        <i :class="'fal fa-' + child.icon"></i>
        <v-list-item-content
          :ref="child.text"
          :class="isElChild ? 'scroll-mamad' : ''"
        >
          <v-list-item-title>
            {{ child.text }}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <folder
        v-else
        :key="child.text"
        :color="!color"
        :sub="true"
        :top="child"
      />
    </template>
    <hr class="hr" />
  </v-list-group>
</template>

<script>
export default {
  name: 'FolderGet',
  props: {
    top: Object,
    sub: { default: false },
    color: { default: false },
  },
  data() {
    return {
      permission: false,
      isEl: false,
      isElChild: false,
    }
  },
  mounted() {
    setTimeout(() => {
      let parent = this.$refs[this?.top?.text]
      let child = parent && parent.children ? parent.children[0] : {}
      this.isEl = parent?.clientWidth < child?.clientWidth + 1
    }, 500)
  },

  methods: {
    standardPermission(url) {
      if (url === undefined) return 0
      return (
        url
          .substr(url.indexOf('/') + 1)
          .split('/')
          .join('-') + '.show'
      )
    },
    dfs(node, visited) {
      if (
        node.to !== undefined &&
        this.$gates.hasPermission(this.standardPermission(node.to))
      ) {
        this.permission = true
      }
      var len = node.children ? node.children.length : 0
      if (visited == null) visited = []

      if ('to' in node && node.to == this.$route.path.replace(/\/+$/, '')) {
        visited.push(node)
        return visited
      }
      for (var i = 0; i < len; i++) {
        var foundNode = this.dfs(node.children[i], visited)
        if (foundNode) {
          visited.push(node)
          return foundNode
        }
      }
      return null
    },
    openSid(child) {
      const stack = this.dfs(child)
      if (stack) {
        return true
      }
      return false
    },
    // expand() {
    //   if (this.folder.leaf) {
    //     return
    //   }

    //   this.folder.expanded = !this.folder.expanded
    // },
    renderChild() {
      this.top.children.forEach((i) => {
        if (i.link) {
          setTimeout(() => {
            let parent = this.$refs[i.text]
            let child = parent.children ? parent.children[0] : {}
            this.isElChild = parent.clientWidth < child.clientWidth + 1
          }, 500)
        }
      })
    },
  },
}
</script>

<style>
.hr {
  border-top: 1px dashed var(--v-blueLogo-base) !important;
  margin: 10px 22px 10px 14px;
}
.ltrMamad .v-list-group__header {
  direction: ltr;
}
.v-list-item__title {
  text-align: right;
  margin-right: 3px;
}
.v-list-item__subtitle,
.v-list-item__title {
  overflow: visible !important;
}
.scroll-mamad .v-list-item__title {
  overflow: hidden !important;
}

.scroll-mamad:hover .v-list-item__title {
  animation-name: scroll-text;
  animation-duration: 4s;
  animation-timing-function: linear;
  animation-delay: 0s;
  animation-iteration-count: infinite;
  animation-direction: normal;
  overflow: initial !important;
}

@keyframes scroll-text {
  0% {
    transform: translateX(0%);
  }
  25% {
    transform: translateX(25%);
  }
  50% {
    transform: translateX(50%);
  }
  75% {
    transform: translateX(75%);
  }
  80% {
    transform: translateX(0%);
  }
  0% {
    transform: translateX(0%);
  }
}
</style>
