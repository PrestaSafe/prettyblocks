<script setup>
import { onMounted, onUnmounted, defineProps, ref, defineComponent } from 'vue'
import emitter from 'tiny-emitter/instance'
import Loader from './Loader.vue'
import Button from './Button.vue'
import Iframe from '../scripts/iframe'
import { contextShop } from '../store/currentBlock'

let iframe = new Iframe(ajax_urls.current_domain, 1, 1)
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

emitter.on('changeUrl', (shop) => {
  iframe.destroy()
  iframe = new Iframe(shop.current_url, shop.id_lang, shop.id_shop)
  iframe.reloadIframe()
})


</script>

<template>
  <!-- animate-pulse classe to put -->
  <section class="w-full h-full">
    <!-- <button @click="reloadIframe()"> reload iframe </button> -->
    <!-- {{ iframe.loader }} -->
    <Loader :visible="iframe.loader.value" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
      Chargement en cours</Loader>
    <!-- {{ classes }} -->

    <iframe id="website-iframe" sandbox="allow-modals allow-forms allow-popups allow-scripts allow-same-origin"
      :class="[height, width]" class="border-none h-full mx-auto rounded" :src="iframe.current_url.value"
      frameborder="0"></iframe>
  </section>
</template>

<style>
#website-iframe {
  transition: all 0.5s ease;
}
</style>