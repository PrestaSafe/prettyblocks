<script setup>
import { defineComponent, defineEmits, watch, ref } from "vue";
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";

let props = defineProps({
  currentTpl: String,
  availableTpl: Object,
  label: String,
  modelValue: Array
})
let currentChoice = ref(props.modelValue)
const emit = defineEmits(['update:modelValue'])

function onChange(e) {
  emit('update:modelValue', e)
  emit('update:currentTpl', e)
}

watch(currentChoice, onChange)
defineComponent({
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
})
</script>

<template>
  <div class="flex items-center justify-center">
    <div class="w-full max-w-xs mx-auto">
      <!-- <Button @click="test">TEST {{ props.item }} current choice: {{ currentChoice }}</Button> -->
      <Listbox as="div" class="space-y-1" v-model="currentChoice" v-slot="{ open }">
        <ListboxLabel class="block text-sm leading-5 font-medium text-gray-700">
          {{ props.label }}
        </ListboxLabel>
        <div class="relative">
          <span class="inline-block w-full rounded-md shadow-sm">
            <ListboxButton
              class="cursor-default relative w-full rounded-md border border-gray-300 bg-white pl-3 pr-10 py-2 text-left focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition ease-in-out duration-150 sm:text-sm sm:leading-5">
              <span class="block truncate">
                {{ currentChoice }}
              </span>
              <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                  <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </span>
            </ListboxButton>
          </span>
          <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="open" class="absolute mt-1 w-full rounded-md bg-white shadow-lg">
              <ListboxOptions static
                class="max-h-60 rounded-md py-1 text-base leading-6 shadow-xs overflow-auto focus:outline-none sm:text-sm sm:leading-5">
                <ListboxOption v-for="(value, path) in props.availableTpl" :key="path" :value="path"
                  v-slot="{ selected, active }">
                  <div :class="`${active ? 'text-white bg-blue-600' : 'text-gray-900'
                    } cursor-default select-none relative py-2 pl-8 pr-4`">
                    <span :class="`${selected ? 'font-semibold' : 'font-normal'
                      } block truncate`">
                      {{ path }}
                    </span>
                    <span v-if="selected" :class="`${active ? 'text-white' : 'text-blue-600'
                      } absolute inset-y-0 left-0 flex items-center pl-1.5`">
                      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </div>
                </ListboxOption>
              </ListboxOptions>
            </div>
          </transition>
        </div>
      </Listbox>
    </div>
  </div>
</template>
