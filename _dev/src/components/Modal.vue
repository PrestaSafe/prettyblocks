<script setup>
import { ref, defineComponent } from 'vue'
import emitter from 'tiny-emitter/instance'
import { HttpClient } from "../services/HttpClient";
import Block from './Block.vue'
import { currentZone } from '../store/currentBlock'
import { trans } from '../scripts/trans'

defineComponent({
  Block
})

let showModal = ref(false)
let blocks = ref([])

const toggleModal = () => {
  showModal.value = !showModal.value;
}

emitter.on('toggleModal', async (zone_name) => {
  toggleModal()
  getBlocksAvailable()
  let current_zone = currentZone()
  await current_zone.$patch({
    name: zone_name,
  })
})

const getBlocksAvailable = () => {
  HttpClient.get(ajax_urls.blocks_available)
    .then((data) => blocks.value = data.blocks)
    .catch(error => console.error(error));
}

</script>

<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center items-center p-4 bg-black/50">
    <div class="flex flex-col w-full max-w-5xl max-h-full bg-white rounded-lg shadow-lg">
      <!-- Header -->
      <div class="flex items-start justify-between p-5 border-b border-solid border-slate-200">
        <h2 class="text-2xl font-semibold">{{ trans('avalaible_elements') }}</h2>
      </div>
      <!-- Body -->
      <div class="overflow-y-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5">
        <Block v-for="block in blocks" :key="block.code" :icon_path="block.icon_path" :name="block.name" :icon="block.icon" :description="block.description" :code="block.code" />
      </div>
      <!-- Footer -->
      <div class="flex items-center justify-end p-5 border-t border-solid border-slate-200">
        <button
          class="text-indigo bg-transparent border border-solid border-indigo hover:bg-indigo hover:text-white active:bg-indigo font-bold uppercase text-sm px-6 py-3 rounded outline-none focus:outline-none ease-linear transition-all duration-150"
          type="button" v-on:click="toggleModal()">
          {{ trans('close') }}
        </button>
      </div>
    </div>
  </div>
</template>
