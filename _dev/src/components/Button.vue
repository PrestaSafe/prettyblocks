<script setup>
import Icon from './Icon.vue'

defineProps({
  icon: String,
  type: String, // primary or secondary
})
</script>

<template>
  <button type="button" :class="[
    'inline-flex items-center gap-2 px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo',
    {
      'border-gray-300 text-gray-700 bg-white hover:bg-gray-50':
        type == 'secondary',
      'border-transparent text-white bg-indigo hover:bg-indigo':
        type !== 'secondary',
    },
  ]">
    <Icon :name="icon" class="h-5 w-5 -ml-1" aria-hidden="true"></Icon>
    <slot></slot>
  </button>
</template>
