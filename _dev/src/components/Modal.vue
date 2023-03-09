<script setup>
import { ref, defineComponent } from 'vue'
import emitter from 'tiny-emitter/instance'
import axios from 'axios'
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
  axios.get(ajax_urls.blocks_available).then((response) => blocks.value = response.data.blocks)
}
</script>

<template>
  <div>
    <!-- <button class="bg-pink-500 text-white active:bg-pink-600 font-bold uppercase text-sm px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" @click="toggleModal()">
            Open large modal
          </button> -->
    <div v-if="showModal"
      class="overflow-x-hidden overflow-y  fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center flex">
      <div class="relative w-auto my-6 mx-auto max-w-[90%] max-h-full">
        <!--content-->
      <div class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none">
        <!--header-->
          <div class="flex items-start justify-between p-5 border-b border-solid border-slate-200 rounded-t">
            <h3 class="text-3xl font-semibold">
              {{ trans('avalaible_elements') }}
            </h3>
            <button
              class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none"
              @click="toggleModal()">
              <span class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none">
                ×
              </span>
            </button>
          </div>
          <!--body-->
          <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 p-4 overflow-auto" style="cursor: auto;">
              <!-- {{ blocks }} -->
              <Block v-for="block in blocks" :key="block.code" :name="block.name" :icon="block.icon"
                :description="block.description" :code="block.code" />
            </div>
          </div>
          <!--footer-->
          <div class="flex items-center justify-end p-6 border-t border-solid border-slate-200 rounded-b">
            <button
              class="text-indigo bg-transparent border border-solid border-indigo hover:bg-indigo hover:text-white active:bg-indigo font-bold uppercase text-sm px-6 py-3 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
              type="button" v-on:click="toggleModal()">
              {{ trans('close') }}
            </button>
            <!-- <button class="text-indigo background-transparent font-bold uppercase px-6 py-2 text-sm outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button" @click="toggleModal()">
                            Save Changes
                          </button> -->
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
  </div>
</template>
