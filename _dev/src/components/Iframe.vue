<script setup>
import { onMounted, onUnmounted, defineProps, ref, defineComponent, computed, watch } from 'vue'

import Loader from './Loader.vue'
import Button from './Button.vue'
import Iframe from '../scripts/iframe'
import { contextShop, usePrettyBlocksContext } from '../store/pinia'
import { storeToRefs } from 'pinia'

const prettyBlocksContext = usePrettyBlocksContext()
const { currentBlock } = storeToRefs(prettyBlocksContext)
const width = computed(() => prettyBlocksContext.iframe.width)
const height = computed(() => prettyBlocksContext.iframe.height)
const device = computed(() => prettyBlocksContext.iframe.device)
const showLoader = computed(() => prettyBlocksContext.iframe.loader)

let iframe_sandbox = ref(window.prettyblocks_env.iframe_sandbox)
defineProps({
  src: String
})
defineComponent({
  Loader, Button
})



const events = ['dragenter', 'dragover', 'dragleave', 'drop']
const preventDefaults = (e) => {
  e.preventDefault()
}


let currentPrettyBlocksId = computed(() => currentBlock.id_prettyblocks)
/**
 * onMounted events
 */
onMounted(() => {

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      prettyBlocksContext.setIframe()
    }, 200)
  })
})

watch(currentBlock, (newVal, oldValue) => {
  prettyBlocksContext.sendPrettyBlocksEvents('scrollInIframe', prettyBlocksContext.currentBlock.id_prettyblocks)
},  {deep: true})

onUnmounted(() => {
  events.forEach((eventName) => {
    document.body.removeEventListener(eventName, preventDefaults)
  })
})
const reloadIframe = () => {
  prettyBlocksContext.reloadIframe()
}


let filteredURL = ref(prettyBlocksContext.updateFilteredURL(ajax_urls.startup_url))

watch(prettyBlocksContext.psContext, () => {
  filteredURL.value = prettyBlocksContext.updateFilteredURL(prettyBlocksContext.psContext.current_url)
})

</script>

<template>
  <!-- animate-pulse classe to put -->
  <section class="w-full h-full">
    <!-- {{ filteredURL }} -->
    <iframe id="website-iframe" :sandbox="iframe_sandbox"
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