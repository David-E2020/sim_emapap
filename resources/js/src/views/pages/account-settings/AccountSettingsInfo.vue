<template>
  <v-card flat class="pa-3 mt-2">
    <v-form class="multi-col-validation">
      <v-card-text class="pt-5">
        <v-row>
          <v-col cols="12">
            <v-textarea v-model="optionsLocal.address" outlined disabled rows="1" label="Dirección"></v-textarea>
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field v-model="optionsLocal.birthday" outlined disabled dense label="Cumpleaños"></v-text-field>
          </v-col>

          <v-col cols="12" md="6">
            <v-text-field v-model="optionsLocal.phone" outlined disabled dense label="Celular"></v-text-field>
          </v-col>

          <v-col cols="12" md="6">
            <p class="text--primary mt-n3 mb-2">Sistemas</p>

            <v-row justify="start" >
              <div  v-for="(det, index) in optionsLocal.sistemas" :key="index">
                <v-chip class="ma-2" :color="det.activado ? 'primary' : ''">
                  {{ det.sistema }}
                </v-chip>
              </div>
            </v-row>
          </v-col>

          <v-col cols="12" md="6">
            <p class="text--primary mt-n3 mb-2">Genero</p>
            <v-radio-group v-model="optionsLocal.gender" row disabled class="mt-0" hide-details>
              <v-radio value="M" label="Masculino"> </v-radio>
              <v-radio value="F" label="Femenino"> </v-radio>
              <v-radio value="O" label="Otro"> </v-radio>
            </v-radio-group>
          </v-col>

          <v-col cols="12" md="6"> </v-col>

          <v-col cols="12" md="6"> </v-col>
        </v-row>
      </v-card-text>

      <!---
      <v-card-text>
        <v-btn
          color="primary"
          class="me-3 mt-3"
        >
          Save changes
        </v-btn>
        <v-btn
          outlined
          class="mt-3"
          color="secondary"
          type="reset"
          @click.prevent="resetForm"
        >
          Cancel
        </v-btn>
      </v-card-text>

      -->
    </v-form>
  </v-card>
</template>

<script>
import { ref } from '@vue/composition-api'

export default {
  props: {
    informationData: {
      type: Object,
      default: () => {},
    },
  },
  setup(props) {
    const optionsLocal = ref(JSON.parse(JSON.stringify(props.informationData)))

    const resetForm = () => {
      optionsLocal.value = JSON.parse(JSON.stringify(props.informationData))
    }

    return { optionsLocal, resetForm }
  },
}
</script>
