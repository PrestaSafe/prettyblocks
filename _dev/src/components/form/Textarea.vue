<script setup>
const props = defineProps({
  title: String,
  placeholder: String,
  name: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    default: false
  },
  modelValue: String | Number
})

const emit = defineEmits(['update:modelValue'])

function onInput(event) {
  emit('update:modelValue', event.target.value)
}
</script>

<template>
  <div>
    <label for="price" class="block text-sm font-medium text-gray-700">{{ title }}</label>
    <div class="mt-1 relative rounded-md shadow-sm">
      <textarea @input="onInput" :value="props.modelValue" :name="name" :id="name" rows="5"
        class="shadow-sm focus:ring-indigo focus:border-indigo mt-1 block w-full sm:text-sm border border-gray-300 rounded-md resize-none"
        :placeholder="placeholder" :required="required" />
    </div>
  </div>
</template>
