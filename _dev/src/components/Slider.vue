<script setup>
import { ref } from 'vue'

let value = ref(50)

const updateValue = (event) => {
  value.value = event.target.value
}
let type = ref('int')
let step = ref(1)

const props = defineProps({
  value: {
    type: Number,
    required: true
  },
  type: {
    type: String,
    default: 'int',
    validator: (value) => ['int', 'step'].includes(value)
  },
  step: {
    type: Number,
    default: 1
  }
})

</script>

<template>
  <div class="slider">
    <input class="bg-indigo" type="range" step="2" min="4" max="8" v-model="value" @input="updateValue" />
    <span>{{ value }}</span>
  </div>
</template>

<style>
.slider {
  width: 100%;
}

.slider input[type="range"] {
  width: 100%;
}
</style>