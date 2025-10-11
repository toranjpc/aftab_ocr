<template>
  <div v-if="breads.length">
    <v-breadcrumbs
      style="padding-top: 7px"
      class="pr-0 pr-md-2"
      :items="breads"
    >
      <template v-slot:divider>
        <v-icon size="12px">fal fa-chevron-double-left</v-icon>
      </template>
    </v-breadcrumbs>
  </div>
</template>

<script>
import items from '@/helpers/dashboardItems'

export default {
  data() {
    return {
      items,
      breads: [],
    }
  },
  watch: {
    $route(to, from) {
      this.calc()
    },
  },
  created() {
    this.calc()
  },
  methods: {
    dfs(node, visited) {
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
    calc() {
      let stack = []
      for (var i = 0; i < this.items.length; i++) {
        stack = this.dfs(this.items[i])
        if (stack) break
      }
      stack = stack ? stack : []
      stack = stack.map((s) => ({
        text: s.text,
        disabled: true,
        href: s.to,
      }))
      this.breads = stack.reverse()
      return stack
    },
  },
}
</script>

<style>
.v-breadcrumbs li {
  font-size: 12pt !important;
  font-weight: bold;
}
</style>
