<script setup>
import { defineProps, ref, defineComponent } from "vue";
import Icon from "./Icon.vue";
import { useCurrentZone } from "../store/pinia";
import Loader from "./Loader.vue";
import emitter from "tiny-emitter/instance";
import { createToaster } from "@meforma/vue-toaster";
import { contextShop, usePrettyBlocksContext } from "../store/pinia";
import { HttpClient } from "../services/HttpClient.js";
import { trans } from "../scripts/trans";

let prettyBlocksContext = usePrettyBlocksContext()

const toaster = createToaster({
  position: "top",
});

defineProps({
  name: String,
  description: String,
  code: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    default: "PuzzleIcon",
  },
  icon_path: {
    type: String,
    default: ''
  }
})

defineComponent({
  Icon,
  Loader,
});

let showLoader = ref(false);

const AddOnZOne = async (code) => {
  let current_zone = prettyBlocksContext.currentZone;
  showLoader.value = true;

  const params = {
    action: "insertBlock",
    code: code,
    ajax: true,
    zone_name: current_zone.name,
    ctx_id_lang: prettyBlocksContext.psContext.id_lang,
    ctx_id_shop: prettyBlocksContext.psContext.id_shop,
    ajax_token: security_app.ajax_token,
  };
  let url = ajax_urls.block_action_urls;
let data = await HttpClient.get(url, params);

  prettyBlocksContext.emit("toggleModal");  
  prettyBlocksContext.initStates();
  prettyBlocksContext.displayMessage(trans('element_added'));
  prettyBlocksContext.reloadIframe();

};
</script>

<template>
  <div class="flex items-center gap-x-2 p-4 bg-gray-100 hover:bg-gray-200 rounded cursor-pointer transition-colors" @click="AddOnZOne(code)">
    <Icon v-if="icon_path == ''" :name="icon" class="h-10 w-10 shrink-0 text-indigo" />
    <img v-else :src="icon_path" class="max-h-16 max-w-16 shrink-0 text-indigo" />
    <div class="flex-1">
      <h3 class="text-lg font-bold">{{ name }}</h3>
      <p class="text-sm text-gray-600">{{ description }}</p>
    </div>
  </div>
</template>
