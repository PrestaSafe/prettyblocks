<script setup>
import { defineComponent, defineProps, ref } from 'vue'
import Collapsable from './Collapsable.vue'
import MenuItem from './MenuItem.vue'
import Icon from './Icon.vue'

defineComponent({
  Collapsable,
  MenuItem,
  Icon
})

const props = defineProps({
  title: String,
  icon: String,
  open: {
    type: Boolean,
    default: false
  }
})

let collapsed = ref(props.open)
const openTab = () => {
  collapsed.value = !collapsed.value
}
</script>

<template>
  <div>
    <div @click="openTab()" class="border-b-2  border-slate-100 p-4">
      <span>{{ title }}</span>
      <span class="block float-right border rounded-full border-slate-500 h-100">
        <Icon v-if="!collapsed" name="ChevronRightIcon" />
        <Icon v-if="collapsed" name="ChevronDownIcon" />
      </span>
    </div>
    <Transition>
      <div v-if="collapsed" class="px-2 bg-slate-50 py-4">
        <slot></slot>
      </div>
    </Transition>
  </div>
</template>

<style>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.2s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
