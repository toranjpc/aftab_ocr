<template>
  <v-menu bottom min-width="150px" rounded offset-y>
    <template #activator="{ on }">
      <v-btn large v-on="on" elevation="0" dark class="yellowLogo mx-1" icon>
        <v-icon size="22" color="blueLogo">fal fa-user-circle</v-icon>
      </v-btn>
    </template>
    <v-card>
      <v-list-item-content class="justify-center py-0">
        <div class="mx-auto text-center">
          <h4 class="pa-3">{{ $auth.user.name }} {{ $auth.user.family }}</h4>
          <v-divider></v-divider>
          <v-btn block text link to="/logout">
            <v-icon>mdi-logout</v-icon>
            <span class="mr-2">
              خروج
            </span>
          </v-btn>
        </div>
      </v-list-item-content>
    </v-card>
  </v-menu>
</template>

<script>
export default {
  name: 'DashboardUserMenu',
}
</script>
