<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  title: String,
  name: {
    type: String,
    required: true
  },
  value: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  modelValue: String
})

const emit = defineEmits(['update:modelValue'])

function onInput(event) {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div class="flex items-center">
    <input @change="onInput" :value="props.value" :checked="props.modelValue == props.value" :name="name" :id="name"
      type="radio" class="h-4 w-4 text-indigo focus:ring-indigo border-gray-300 rounded-full" :required="required" />
    <label :for="name" class="ml-2 block text-sm text-gray-900">
      {{ title }}
    </label>
  </div>
</template>
