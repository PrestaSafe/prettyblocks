<script setup>
import Accordion from './Accordion.vue'
import { HttpClient } from '../services/HttpClient'
import FieldRepeater from './FieldRepeater.vue'
import { defineComponent, onMounted, onUnmounted, ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import { createToaster } from "@meforma/vue-toaster";

import { contextShop } from "../store/currentBlock";

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
onMounted(()=> {
   getSettings()
  // console.log('mounted PanelSettings')
})
emitter.on('globalSave', () => {
  if (canSave.value) {
    saveThemeSettings()
  }
})

const canSave = ref(false)
const settings = ref(false)

const getInputs = () => {
  emitter.on('initStates', async () => {
   getSettings()
  })

}

const getSettings = async () => {
   let contextStore = contextShop();
    let context = await contextStore.getContext();
    const params = {
      ajax: true,
      ctx_id_lang: context.id_lang,
      ctx_id_shop: context.id_shop,
    };
    HttpClient.get(ajax_urls.theme_settings, params)
      .then((data) => {
        canSave.value = true
        settings.value = data.settings
        // console.log('data settings', data.settings)
      })
      .catch(error => console.error(error));
}


getInputs()

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

const saveThemeSettings = async () => {
  let contextStore = contextShop();
  let context = await contextStore.getContext();
  // console.log('save context', context)
  // console.log('urls', ajax_urls.state)
  const params = {
    action: 'updateThemeSettings',
    ajax: true,
    ctx_id_shop: context.id_shop,
    ctx_id_lang: context.id_lang,
    stateRequest: settings.value,
    ajax_token: security_app.ajax_token
  }

HttpClient.post(ajax_urls.state, params)
  .then((data) => {
    // console.log('data', data)
    if (data.message) {
      toaster.show(data.message)
      emitter.emit('reloadIframe')
    }
  })
  .catch(error => console.error(error));
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
