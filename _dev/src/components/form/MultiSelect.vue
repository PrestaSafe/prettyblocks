<script setup>
import { defineComponent, defineEmits, ref, watch } from 'vue'
import VueFormMultiselect from '@vueform/multiselect'

const props = defineProps({
  label: String,
  mode: String,
  modelValue: Array,
  options: Array,
  searchable: Boolean,
})

defineComponent({
  VueFormMultiselect,
})

let currentOptions = ref(props.modelValue)
const emit = defineEmits(['update:modelValue'])

function onChange(e) {
  emit('update:modelValue', e)
}

watch(currentOptions, onChange)
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<template>
  <div class="text-sm font-medium text-gray-700 my-1">{{ label }}</div>
  <VueFormMultiselect
    v-model="currentOptions"
    :mode="mode"
    :options="options"
    :searchable="searchable"
    class="mb-2"
  />
</template>
