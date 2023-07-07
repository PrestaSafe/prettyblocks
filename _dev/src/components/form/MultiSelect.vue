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

let currentOption = ref(props.modelValue)
const emit = defineEmits(['update:modelValue'])

function onChange(e) {
  emit('update:modelValue', e)
}

watch(currentOption, onChange)
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<template>
  <div class="text-sm font-medium text-gray-700 mb-1">{{ label }}</div>
  <VueFormMultiselect
    v-model="currentOption"
    :mode="mode"
    :options="options"
    :searchable="searchable"
  />
</template>
