<script setup>
import { ref } from "vue";

const isCollapsed = ref(true)
</script>

<template>
  <div class="flex flex-col">
    <slot name="header" :isCollapsed="isCollapsed" :collapse="() => isCollapsed = !isCollapsed"></slot>
    <div class="ml-10" :class="['collapsable', { 'collapsed': isCollapsed }]">
      <slot name="content"></slot>
    </div>
  </div>
</template>

<style scoped>
.collapsable {
  @apply overflow-hidden;
}

.collapsed {
  @apply h-0;
}
</style>
