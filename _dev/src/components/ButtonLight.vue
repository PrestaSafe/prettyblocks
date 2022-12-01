<script setup>
import Icon from './Icon.vue'

defineProps({
  icon: String,
  size: {
    type: Number,
    default: 5
  }
})
</script>

<template>
  <button
    type="button"
    class="inline-flex items-center gap-2 p-1 hover:bg-black hover:bg-opacity-10 rounded"
  >
    <Icon :name="icon" :size="size"></Icon>
    <slot></slot>
  </button>
</template>
