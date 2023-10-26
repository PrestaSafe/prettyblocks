<script setup>
import { onMounted, onUnmounted, defineProps, ref, defineComponent, computed, watch } from 'vue'
import emitter from 'tiny-emitter/instance'
import Loader from './Loader.vue'
import Button from './Button.vue'
import Iframe from '../scripts/iframe'
import { contextShop } from '../store/currentBlock'

let iframe = new Iframe(ajax_urls.startup_url, 1, 1)
defineProps({
  src: String
})
defineComponent({
  Loader, Button
})

const width = ref('w-full')
const height = ref('h-full')
emitter.off('changeIframeSize')
emitter.on('changeIframeSize', (w, h) => {
  width.value = w
  height.value = h
})

const events = ['dragenter', 'dragover', 'dragleave', 'drop']
const preventDefaults = (e) => {
  e.preventDefault()
}
onMounted(() => {
  events.forEach((eventName) => {
    document.body.addEventListener(eventName, preventDefaults)
  })
  iframe.loadIframe()
})

onUnmounted(() => {
  events.forEach((eventName) => {
    document.body.removeEventListener(eventName, preventDefaults)
  })
})
const reloadIframe = () => {
  iframe.reloadIframe()
}
emitter.on('reloadIframe', async (id_prettyblocks) => {
  let context = contextShop()
  iframe.setUrl(context.href)
  iframe.reloadIframe()
})

emitter.on('highLightBlock', (id_prettyblocks) => {
  iframe.sendPrettyBlocksEvents('selectBlock', { id_prettyblocks: id_prettyblocks })
})

/**
 * Change url with loader in iframe.
 */
emitter.on('changeUrl', (shop, custom_url = null) => {  
  if(custom_url == null)
  {
    iframe.setUrl(shop.current_url)
  }else{
    iframe.setUrl(custom_url)
  }

  iframe.setIdLang(shop.id_lang)
  iframe.setIdShop(shop.id_shop)
  iframe.constructEvent()
  iframe.reloadIframe()
})



let showLoader = computed(() => {
  return iframe.loader.value
})

 watch(iframe.loader);
let filteredURL = computed(() => {
  return iframe.updateFilteredURL(iframe.current_url.value)
})


watch(iframe.current_url, () => {
  filteredURL.value = iframe.updateFilteredURL(iframe.current_url.value)
})

</script>

<template>
  <!-- animate-pulse classe to put -->
  <section class="w-full h-full">
    <!-- <button @click="reloadIframe()"> reload iframe </button> {{ showLoader }} -->
    <!-- {{ classes }} -->
    <iframe id="website-iframe" sandbox="allow-modals allow-forms allow-popups allow-scripts allow-same-origin"
      :class="[height, width, showLoader ? 'opacity-50' : '']" class="border-none h-full mx-auto rounded" :src="filteredURL"
      frameborder="0"></iframe>
    <Loader :visible="showLoader" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
      Chargement en cours</Loader>
  </section>
</template>

<style>
#website-iframe {
  transition: all 0.5s ease;
}
</style>