<script setup>
import _get from 'lodash/get'
</script>
<template>
  <div>
    <!-- یک عدد تنها -->
    <ul v-if="!top.isGroup">
      <li class="group mb-1 min-h-[2rem] pr-1">
        <NuxtLink :to="top.to">
          <a class="nui-focus relative top-0.5 flex items-center">
            <span
              :class="top.to == $route.path ? 'text-primary-500' : ''"
              class="text-muted-400 group-hover:text-primary-500 relative inline-flex items-center gap-2 font-sans text-[13px] whitespace-nowrap transition-colors duration-300"
            >
              <span>{{ top.text }} </span></span
            >
          </a>
        </NuxtLink>
      </li>
      <!-- برای جدا کردن فسمت‌ها با موضوع متفاوت -->
      <li
        v-if="top.hr"
        class="border-muted-200 dark:border-muted-700 mb-2 h-px w-full border-t"
      ></li>
    </ul>
    <!-- اگر زیر مجموعه داشته باشد -->
    <ul v-else>
      <li v-show="permission" class="mb-1 min-h-[2rem] pr-1 cursor-pointer">
        <span @click.stop="openLi2 = !openLi2">
          <div class="group nui-focus relative top-0.5 flex items-center">
            <i
              v-if="_get(top, 'icon', 0)"
              :class="_get(top, 'icon', 'null')"
              class="text-muted-400 group-hover:text-primary-500 pl-1"
            ></i>
            <span
              v-if="activeLink"
              class="bg-primary-500 absolute -start-2 top-2 h-1 w-1 rounded-full"
            ></span>
            <span
              class="text-muted-400 group-hover:text-primary-500 relative inline-flex items-center gap-2 font-sans text-[13px] whitespace-nowrap transition-colors duration-300"
            >
              {{ top.text }}
            </span>
            <svg
              width="1em"
              height="1em"
              viewBox="0 0 24 24"
              class="icon text-muted-400 ms-auto block h-4 w-4 transition-transform duration-300 icon"
              :class="openLi2 ? ' rotate-180' : ''"
            >
              <path
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m6 9l6 6l6-6"
              ></path>
            </svg>
          </div>
        </span>
        <div
          class="transition-all duration-150 opacity-100 mt-3 pr-1"
          :class="openLi2 ? 'test-down ' : 'test-up'"
          :style="'max-height:' + top.childrenCount + 'px'"
        >
          <ul class="py-2 max-h-max pr-1">
            <template v-for="(child, i) in top.children">
              <li
                :key="i"
                :class="
                  openLi2
                    ? `-translate-x-[100px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards`
                    : ''
                "
                :style="{ animationDelay: (i + 1) / 30 + 's' }"
                v-show="!child.isGroup"
                class="group mb-1 min-h-[2rem] pr-1 cursor-pointer"
              >
                <NuxtLink :to="child.to">
                  <div class="nui-focus relative top-0.5 flex items-center">
                    <i
                      v-if="_get(child, 'icon', 0)"
                      :class="_get(child, 'icon', 'null')"
                      class="text-muted-400 group-hover:text-primary-500 pl-1"
                    ></i>
                    <span
                      :class="child.to == $route.path ? 'text-primary-500' : ''"
                      class="text-muted-400 group-hover:text-primary-500 relative inline-flex items-center gap-2 font-sans text-[13px] whitespace-nowrap transition-colors duration-300"
                    >
                      <span
                        :class="isElChild ? 'scroll-mamad' : ''"
                        :ref="child.text"
                      >
                        {{ child.text }}</span
                      ></span
                    >
                  </div>
                </NuxtLink>
              </li>
              <folder
                :class="
                  openLi2
                    ? `-translate-x-[100px] animate-[0.4s_ease-in-out_0.1s_intro-menu] animate-fill-mode-forwards`
                    : ''
                "
                :style="{ animationDelay: (i + 1) / 30 + 's' }"
                v-show="child.isGroup"
                :color="!color"
                :sub="true"
                :top="child"
              />
            </template>
          </ul>
        </div>
      </li>
      <li
        v-if="top.hr"
        class="border-muted-200 dark:border-muted-700 my-3 h-px w-full border-t"
      ></li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'folder',
  data() {
    return {
      openLi2: false,
      permission: false,
      isEl: false,
      isElChild: false,
      activeLink: false,
    }
  },
  props: {
    top: Object,
    sub: { default: false },
    color: { default: false },
    index: { default: 0 },
  },
  created() {
    let stack
    stack = this.dfs(this.top)
    this.addChildrenCount(this.top)
    if (stack) {
      this.activeLink = true
      return (this.openLi2 = true)
    }
    return (this.openLi2 = false)
  },
  mounted() {
    setTimeout(() => {
      let parent = this.$refs[this?.top?.text]
      let child = parent?.children[0]
      this.isEl = parent?.clientWidth < child?.clientWidth + 1
    }, 500)
  },

  methods: {
    countChildren(node) {
      let count = 0
      if (node.children && node.children.length > 0) {
        count += node.children.length * 36 + 16
        for (const child of node.children) {
          count += this.countChildren(child)
        }
      }
      return count
    },

    addChildrenCount(node) {
      if (this.countChildren(node) > 0) {
        node.childrenCount = this.countChildren(node)
      }
      if (node.children && node.children.length > 0) {
        for (const child of node.children) {
          this.addChildrenCount(child)
        }
      }
    },
    dfs(node, visited) {
      if ('to' in node) {
        this.permission = true
      } else this.permission = false

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

    expand() {
      if (this.folder.leaf) {
        return
      }
      this.folder.expanded = !this.folder.expanded
    },
  },
}
</script>
<style>
.test-up {
  max-height: 0 !important;
  overflow: hidden;
  transition: max-height 1s;
}
.test-down {
  /* max-height: 600px; */
  overflow: hidden;
  transition: max-height 1s;
}
</style>
