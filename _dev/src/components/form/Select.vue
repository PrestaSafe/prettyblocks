<script setup>
import { onMounted, reactive, watch } from 'vue'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { ChevronUpDownIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  title: String,
  // api url to get the data
  apiUrl: String,
  // given one entry returned by the API, which field to show in select ?
  displayField: {
    type: Function,
    default: (entry) => {}
  },
  modelValue: Object,
  useLocal: {
    type: Boolean,
    default: false
  },
  items: Object
})

const state = reactive({
  items: []
})

async function fetchItems() {
  if(!props.useLocal){
    const response = await fetch(props.apiUrl)
    state.items = await response.json()
  }else{
    state.items = props.items
  }

  // if no initial value provided
  // set selection as first item in the list
  if(!props.modelValue && state.items.length > 0)
    emit('update:modelValue', state.items[0])
}

onMounted(() => {
  fetchItems()
})

const emit = defineEmits(['update:modelValue'])

function onInput(value) {
  emit('update:modelValue', value)
}

watch(() => props.modelValue, onInput)
</script>

<template>
  <Listbox as="div" @update:model-value="onInput" :value="props.modelValue">
    <ListboxLabel class="block text-sm font-medium text-gray-700">{{ title }}</ListboxLabel>
    <div class="mt-1 relative">
      <ListboxButton class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo focus:border-indigo sm:text-sm">
        <span class="flex items-center">
          <span class="block truncate">{{ displayField(props.modelValue) }}</span>
        </span>
        <span class="ml-3 absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </span>
      </ListboxButton>

      <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <ListboxOptions class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-56 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
          <ListboxOption as="template" v-for="(item, index) in state.items" :key="index" :value="item" v-slot="{ active, selected }">
            <li :class="[active ? 'text-white bg-indigo' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-3 pr-9']">
              <div class="flex items-center">
                <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                  {{ displayField(item) }}
                </span>
              </div>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>
