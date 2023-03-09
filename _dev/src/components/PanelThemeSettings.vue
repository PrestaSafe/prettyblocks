<script setup>
import Accordion from './Accordion.vue'
import axios from 'axios'
import FieldRepeater from './FieldRepeater.vue'
import { defineComponent, onMounted, onUnmounted, ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({
  position: 'top',
});

defineComponent({
  Accordion,
  FieldRepeater
})

onUnmounted(() => {
  settings.value = false
  canSave.value = false
})

emitter.on('globalSave', () => {
  if (canSave.value) {
    saveThemeSettings()
  }
})

const canSave = ref(false)
const settings = ref(false)

const getInputs = () => {
  axios.get(ajax_urls.theme_settings)
    .then((response) => response.data)
    .then((data) => {
      canSave.value = true
      settings.value = data.settings
    })
}

getInputs()

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const saveThemeSettings = () => {
  const params = {
    action: 'updateThemeSettings',
    ajax: true,
    stateRequest: settings.value,
    ajax_token: security_app.ajax_token
  }
  axios.post(ajax_urls.state, params).then((response) => response.data)
    .then((data) => {
      if (data.message) {
        toaster.show(data.message)
        emitter.emit('reloadIframe')
      }
    })
}
</script>

<template>
  <div>
    <Accordion v-for="(form, tab) in settings" :key="form" :title="capitalizeFirstLetter(tab)">
      <div v-for="f in form" :key="f">
        <FieldRepeater @updateUpload="saveThemeSettings()" :field="f" />
      </div>
    </Accordion>
  </div>
</template>
