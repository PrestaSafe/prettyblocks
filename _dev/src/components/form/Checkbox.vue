<script setup>
const props = defineProps({
  title: String,
  name: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

function onInput(event) {
  emit('update:modelValue', event.target.checked)
}
</script>

<template>
  <div class="flex items-center">
    <input @change="onInput" :checked="props.modelValue" :name="name" :id="name" type="checkbox"
      class="h-4 w-4 text-indigo focus:ring-indigo border-gray-300 rounded" :required="required" />
    <label :for="name" class="ml-2 block text-sm text-gray-900">
      {{ title }}
    </label>
  </div>
</template>
