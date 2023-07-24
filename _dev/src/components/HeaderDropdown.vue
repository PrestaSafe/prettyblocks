<script setup>
import { reactive, watch } from "vue"
import { HttpClient }  from '../services/HttpClient.js'

import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/24/solid'
import Input from './form/Input.vue';

const props = defineProps({
  title: String,
  label: String,
  selector: String,
  // a method that return the formated API url, given the search query
  apiUrl: {
    type: Function,
    default: (query) => ajax_urls.collection
  },
  // given one row returned by the API, which field to show on page ?
  displayField: {
    type: Function,
    default: (entry) => { return entry.show?.formatted }
  },
  collection: {
    type: String,
  },
  modelValue: Object
})

const search = reactive({
  // search query
  query: "",
  // results from api
  results: [],
  modelValue: {}
})


const emit = defineEmits(['update:modelValue'])

async function fetchResults() {
  const params = {
    query: search.query,
    collection: (props.collection) ? props.collection : 'Category',
    selector: (props.selector) ? props.selector : '{id} - {name}',
    ajax: true,
  };

  const json = await HttpClient.get(props.apiUrl(search.query), params);

  search.results = json.results
}

function onSelect(value) {
  emit('update:modelValue', value)
}

watch(() => search.query, fetchResults)
</script>

<template>
  <Popover v-slot="{ open, close }" class="relative">
    <label :for="name" v-if="label" class="block text-sm font-medium text-gray-700">{{ label }}</label>
    <PopoverButton :class="open ? '' : 'text-opacity-90'"
      class="w-full inline-flex justify-between items-center gap-2 px-4 py-2 border rounded-md shadow-sm text-sm font-medium text-ellipsis whitespace-nowrap overflow-hidden focus:outline-none focus:ring-0 border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
      <span>{{ modelValue && displayField(modelValue) || title }}</span>
      <ChevronDownIcon :class="open ? '' : 'text-opacity-70'"
        class="ml-2 h-5 w-5 transition duration-150 ease-in-out group-hover:text-opacity-80" aria-hidden="true" />
    </PopoverButton>
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-5 opacity-0"
      enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-5 opacity-0">
      <PopoverPanel class="absolute left-1/2 z-10 mt-3 w-full -translate-x-1/2 transform px-4 sm:px-0 lg:max-w-3xl">
        <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
          <div class="relative bg-white">
            <div class="px-2 pt-1 pb-2">
              <Input v-model="search.query" icon="SearchIcon" />
            </div>
            <ul v-if="search.results.length > 0" class="py-1">
              <li @click="onSelect(result); close()" v-for="(result, index) in search.results" :key="index"
                class="text-gray-700 block px-4 py-2 text-sm cursor-pointer hover:text-white hover:bg-indigo">
                {{ displayField(result) }}

              </li>
            </ul>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>
