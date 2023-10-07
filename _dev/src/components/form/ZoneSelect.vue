<script setup>
import { defineComponent, ref, watch } from 'vue'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { ChevronUpDownIcon } from '@heroicons/vue/24/solid'
import { storedZones, currentZone } from '../../store/currentBlock'
import emitter from 'tiny-emitter/instance'
import { trans } from '../../scripts/trans'

defineComponent({
  Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions,
  ChevronUpDownIcon
})
let items = ref([]);

emitter.on('loadZones', (zonesState) => {

  items.value = zonesState
  if (zonesState.indexOf(currentZone().name) == -1) {
    currentZone().$patch({
      name: zonesState[0]
    })

    props.modelValue.name = zonesState[0]
    onInput(zonesState[0])
  }
})

emitter.on('selectZone' , (zone) => {
  props.modelValue.name = zone
  onInput(zone)
})

const props = defineProps({
  title: String,
  // api url to get the data
  modelValue: {
    type: Object,
    default: {
      name: 'displayHome'
    }
  }

})

const changetItem = (item) => {
  props.modelValue.name = item
}



const emit = defineEmits(['update:modelValue'])

function onInput(value) {
  let current_zone = currentZone()
  current_zone.$patch({ name: value })
  emit('update:modelValue.name', value)

  emitter.emit('focusOnZone', value) 
  
}

watch(() => props.modelValue, onInput)
</script>

<template>
  <Listbox as="div" @update:model-value="onInput" :value="props.modelValue">
    <ListboxLabel class="block text-sm font-medium text-gray-700">{{ title }}</ListboxLabel>
    <div class="mt-1 relative">
      <ListboxButton
        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo focus:border-indigo sm:text-sm">
        <span class="flex items-center">
          <!-- display the name of selected element -->
          <span class="block truncate">{{ trans('current_zone') }}: {{ props.modelValue.name }}</span>
        </span>
        <span class="ml-3 absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </span>
      </ListboxButton>
      <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <ListboxOptions
          class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-56 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
          <ListboxOption as="template" v-for="(item, index) in items" :key="index" :value="item"
            v-slot="{ active, selected }">
            <li @click="changetItem(item)"
              :class="[active ? 'text-white bg-indigo' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-3 pr-9']">
              <div class="flex items-center">
                <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                  {{ item }}
                </span>
              </div>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>
