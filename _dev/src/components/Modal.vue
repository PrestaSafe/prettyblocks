<script setup>
import { ref, defineComponent, onMounted, onBeforeUnmount, computed } from 'vue'

import { HttpClient } from "../services/HttpClient";
import Block from './Block.vue'
import { useCurrentZone, usePrettyBlocksContext } from '../store/pinia'
import { trans } from '../scripts/trans'
import { PresentationChartBarIcon } from '@heroicons/vue/24/solid';
let prettyBlocksContext = usePrettyBlocksContext()
defineComponent({
  Block
})

let showModal = ref(false)
prettyBlocksContext.on('toggleModal', () => {
  toggleModal()
})
let blocks = ref([])
let search = ref('')
let showSearch = ref(false)
let filteredBlocks = computed(() => {
  let results = blocks.value
  if (search.value && typeof blocks.value === 'object') {
    results = {}
    Object.keys(blocks.value).forEach(index => {
      if (
        blocks.value[index].name.includes(search.value) 
        || blocks.value[index].description.includes(search.value)
        || blocks.value[index].code.includes(search.value)
      ) {
        results[index] = blocks.value[index];
      }
    });
  }
    return results
});
const toggleModal = () => {
  showModal.value = !showModal.value;
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && showModal.value) {
    showModal.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
  getBlocksAvailable()
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEscape)
})


/**
 * reload blocks after save settings
 */
prettyBlocksContext.on('afterSaveSettings', () => {
  getBlocksAvailable()
})

const getBlocksAvailable = () => {
  HttpClient.get(ajax_urls.blocks_available)
    .then((data) => {
        blocks.value = data.blocks
        showSearch.value = true
      })
    .catch(error => console.error(error));

}
const prettyblocks_env = ref(window.prettyblocks_env.PRETTYBLOCKS_REMOVE_ADS);
</script>

<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center items-center p-4 bg-black/50">
    <div class="flex flex-col w-full max-w-5xl max-h-full bg-white rounded-lg shadow-lg">
      <!-- Header -->
      <div class="flex items-start justify-between p-5 border-b border-solid border-slate-200">
          <h2 class="text-2xl font-semibold">{{ trans('avalaible_elements') }}</h2>
          <div v-if="showSearch">
            <input type="text" v-model="search" :placeholder="trans('search_blocks')" class="w-full px-3 py-2 placeholder-gray-400 text-gray-700 bg-white rounded text-sm shadow focus:outline-none focus:shadow-outline ease-linear transition-all duration-150" />
          </div>
      </div>
      <!-- Body -->
      
      <div class="overflow-y-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
      <!-- Search Input -->
        <Block v-for="block in filteredBlocks" :key="block.code" :icon_path="block.icon_path" :name="block.name" :icon="block.icon" :description="block.description" :code="block.code" />
      </div>
      <!-- Footer -->
      <div class="flex items-center justify-between p-5 border-t border-solid border-slate-200">
        <a v-if="!prettyblocks_env" href="https://prettyblocks.io/pro" class="text-red-500" target="_blank">{{ trans('get_pro') }}</a>
        <button
          class="text-indigo bg-transparent border border-solid border-indigo hover:bg-indigo hover:text-white active:bg-indigo font-bold uppercase text-sm px-6 py-3 rounded outline-none focus:outline-none ease-linear transition-all duration-150"
          type="button" v-on:click="toggleModal()">
          {{ trans('close') }}
        </button>
      </div>
    </div>
  </div>
</template>
