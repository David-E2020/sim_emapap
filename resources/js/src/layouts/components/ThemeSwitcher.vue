<template>
  <v-fade-transition mode="out-in">
    <v-btn icon @click="toggleTheme" title="Cambiar tema">
      <v-icon :key="$vuetify.theme.dark">
        {{ $vuetify.theme.dark ? icons.mdiWeatherSunny : icons.mdiWeatherNight }}
      </v-icon>
    </v-btn>
  </v-fade-transition>
</template>

<script>
import { mdiWeatherNight, mdiWeatherSunny } from '@mdi/js'

export default {
  setup() {
    return {
      icons: {
        mdiWeatherNight,
        mdiWeatherSunny,
      },
    }
  },
  methods: {
    toggleTheme() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark
      localStorage.setItem('theme_dark', this.$vuetify.theme.dark)
    },
  },
}
</script>

<style>
</style>

