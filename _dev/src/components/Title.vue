<script setup>
const props = defineProps({
  title: String,
  modelValue: String | Number
})

const emit = defineEmits(['update:modelValue'])

function onInput(event) {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div>
    <label  class="block text-sm font-medium text-gray-700">{{ title }}</label>
  </div>
</template>
