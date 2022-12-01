<script setup>
import { reactive } from "vue"

defineProps({
  title: String
})
</script>

<template>
  <div class="flex flex-col gap-2">
    <div class="block text-sm font-medium text-gray-700 mb-1">{{ title }}</div>
    <slot></slot>
  </div>
</template>
