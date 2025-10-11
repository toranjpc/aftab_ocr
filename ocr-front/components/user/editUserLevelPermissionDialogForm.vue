<template>
  <v-card color="white">
    <v-card-title class="pl-0 headline white--text py-1 primary">
      <h6 v-if="!isEditing">افزودن</h6>
      <h6 v-else>ویرایش</h6>
      <v-spacer />
      <v-btn dark text @click="_event('handleDialogForm', false)">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-card-title>
    <v-card-text class="py-3">
      <v-row>
        <v-col cols="12">
          <v-text-field v-model="form.name" outlined dense label="نام دسترسی" />
        </v-col>
        <v-col>
          <v-treeview
            v-model="form.permission_do"
            :items="items"
            selected-color="indigo"
            open-on-click
            selectable
            return-object
            expand-icon="mdi-chevron-down"
            on-icon="mdi-bookmark"
            off-icon="mdi-bookmark-outline"
            indeterminate-icon="mdi-bookmark-minus"
          />
        </v-col>

        <v-divider vertical></v-divider>

        <v-col cols="12" md="6">
          <div
            v-if="form.permission_do.length === 0"
            key="title"
            class="text-h6 font-weight-light grey--text pa-4 text-center"
          >
            دسترسی ها را انتخاب کنید
          </div>

          <v-scroll-x-transition
            group
            hide-on-leave
            class="d-flex flex-wrap flex-row"
          >
            <div
              v-for="(selection, key, i) in groupedPermissions"
              :key="i"
              class="col-6 pa-1"
            >
              <field-set :key="i" :label="key">
                <v-tooltip bottom :key="i" v-for="(s, i) in selection">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn
                      :color="s.color"
                      dark
                      class="mx-1 pa-2"
                      x-small
                      outlined
                      v-bind="attrs"
                      v-on="on"
                      @click="
                        form.permission_do = form.permission_do.filter(
                          (t) => t.key != s.key
                        )
                      "
                    >
                      <v-icon size="14">
                        {{ s.icon }}
                      </v-icon>
                    </v-btn>
                  </template>
                  <span>{{ s.tooltip }}</span>
                </v-tooltip>
              </field-set>
            </div>
          </v-scroll-x-transition>
        </v-col>
      </v-row>
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn
        color="primary"
        outlined
        small
        @click="
          ;[
            _event(isEditing ? 'editTheItem' : 'addTheItem', {
              ...form,
              permission_do: form.permission_do.map((p) => p.key),
            }),
          ]
        "
      >
        <h6 v-if="!isEditing">افزودن</h6>
        <h6 v-else>ویرایش</h6>
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import { get as getSafe } from 'lodash'
import { FieldSet } from 'majra'
import permissions from '@/helpers/permissions'
import { flats } from '@/helpers/permissions'

export default {
  components: { FieldSet },

  props: {
    item: { default: null },
    isEditing: { default: false },
  },

  data() {
    return {
      form: {
        name: '',
        permission_do: [],
      },
      items: permissions,
      flats,
    }
  },

  computed: {
    groupedPermissions() {
      const grouped = {}
      for (const permission of this.form.permission_do) {
        if (permission.name !== undefined) {
          if (!grouped[permission.name.split('|')[0]])
            grouped[permission.name.split('|')[0]] = []
          grouped[permission.name.split('|')[0]].push(permission)
        }
      }
      return grouped
    },
  },

  watch: {
    item(val) {
      if (!this.isEditing) return
      if (val) {
        this.form.id = this.item.id
        this.form.name = this.item.name
        this.form.permission_do = this.getPermissions()
      } else this.form = { name: '', permission_do: [] }
    },
  },

  created() {
    this._listen('createBtn', () => {
      this.form = {
        name: '',
        permission_do: [],
      }
    })
    if (!this.isEditing) return
    if (this.item != null) {
      this.form.id = this.item.id
      this.form.name = this.item.name
      this.form.permission_do = this.getPermissions()
    } else this.form = { name: '', permission_do: [] }
  },

  methods: {
    save() {
      this._event('loading')
      const permis = this.form.permission_do.map((p) => p.key)
      this.$axios
        .$post('/user-level-permission', {
          UserLevelPermission: {
            name: this.form.name,
            permission_do: permis,
          },
        })
        .then((res) => {
          this._event('handleDialogForm', false)
          this._event('alert', {
            text: getSafe(res, 'message'),
            color: 'success',
          })
        })
        .catch((err) => {
          this._event('alert', {
            text: getSafe(err, 'response.data.message'),
            color: 'success',
          })
        })
        .finally(() => {
          this._event('loading', false)
        })
    },
    findName(permission) {
      for (const per of this.flats) {
        if (permission.split('.')[0] == per.key) {
          return per.name
        }
      }
    },
    getColor(permission) {
      const type = permission.split('.')[1]
      switch (type) {
        case 'show':
          return 'info'
        case 'create':
          return 'success'
        case 'edit':
          return 'primary'
        case 'delete':
          return 'error'
      }
    },
    getPermissions() {
      return this.item.permission_do.map((permission) => {
        const name = this.findName(permission)
        const color = this.getColor(permission)
        return {
          name,
          color,
          id: permission,
          key: permission,
        }
      })
    },
  },
}
</script>

<style scoped>
.mamad-scroll {
  display: flex;
  flex: 1 1 100%;
  flex-direction: column;
  max-height: 100%;
  max-width: 100%;
  height: 80%;
}

.mamad-scroll .v-card__text {
  backface-visibility: hidden;
  /* flex: 1 1 auto; */
  overflow-y: auto;
}
</style>
