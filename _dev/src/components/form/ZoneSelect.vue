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
let current_zone = currentZone()
emitter.on('loadZones', (zonesState) => {
  items.value = zonesState
  if (zonesState.indexOf(currentZone().name) == -1) {
    let priorityZone = zonesState[0];

    // if prettyblocks already init (when reloading a simple page)
    let isPresent = zonesState.find(zone => {
      return zone.name === current_zone.zoneToFocus
    }) || false
   
    if(current_zone.zoneToFocus !== ''  && isPresent)
    {
      priorityZone = current_zone
    } else {

      current_zone.$patch({
        name: zonesState[0].name,
        alias: zonesState[0].alias,
        priority: zonesState[0].priority,
        zoneToFocus: zonesState[0].name,
      })
      if (zonesState.some(zone => zone.name === props.modelValue.name)) {
        priorityZone = zonesState.find(zone => zone.name === props.modelValue.name);
      } else {
        priorityZone = zonesState[0];
      }
  
  
      // check if there is a priority zone 
      for (let zone of zonesState) {
          if (zone.priority === true) {
              priorityZone = zone;
              break;
          }
      }
    }

    
    props.modelValue.name = priorityZone.name
    props.modelValue.alias = priorityZone.alias
    props.modelValue.priority = priorityZone.priority
    props.modelValue.zoneToFocus = priorityZone.name

    onInput(priorityZone)
  }
})

emitter.on('selectZone' , (zone) => {
  props.modelValue = zone
  onInput(zone)
})

const props = defineProps({
  title: String,
  // api url to get the data
  modelValue: {
    type: Object,
    default: {
      name: 'displayHome',
      alias: '',
      priority: false
    }
  }

})

const changetItem = (item) => {
  props.modelValue.name = item.name
  props.modelValue.alias = item.alias
  // force reload on the last zone
  props.modelValue.priority = true
}



const emit = defineEmits(['update:modelValue'])

function onInput(zone) {
  let current_zone = currentZone()
  current_zone.$patch({ 
    name: zone.name,
    alias: zone.alias,
    priority: zone.priority,
    zoneToFocus: zone.name,
  })

  emit('update:modelValue.name', zone.name)
  emit('update:modelValue.alias', zone.alias)
  emit('update:modelValue.priority', zone.priority)

  emitter.emit('focusOnZone', zone.name) 
  
}

watch(() => props.modelValue, onInput)
</script>

<template>
  <Listbox as="div" @update:model-value="onInput" :value="props.modelValue">
    <ListboxLabel class="block text-sm font-medium text-gray-700">{{ title }}</ListboxLabel>
    <div class="mt-1 relative">
      <ListboxButton
        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo focus:border-indigo sm:text-sm">
        <span class="flex items-center break-all">
          <!-- display the name of selected element -->
          <span class="block line-clamp-1 truncate max-w-48"> {{ trans('current_zone') }}: {{ props.modelValue.alias !== '' ? props.modelValue.alias : props.modelValue.name }}</span>
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
                  {{ item.alias !== '' ? item.alias : item.name }}
                </span>
              </div>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>
