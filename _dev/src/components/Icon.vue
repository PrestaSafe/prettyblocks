<script setup>
// this component is useful because it allows us to have dynamic icon name
// using the heroicons from tailwindcss https://heroicons.com/

// names : arrow-down --> ArrowDownIcon

// could not get dynamic import working with defineAsyncComponent
// so need to import all the icon for now :(
import * as solid from "@heroicons/vue/24/solid"

const props = defineProps({
  name: String,
  class: {
    type: String,
    default: "h-5 w-5",
  },
})

function getIcon(name) {
  return solid[name]
}
</script>

<template>
  <component :is="getIcon(name)" :class="props.class" />
</template>
