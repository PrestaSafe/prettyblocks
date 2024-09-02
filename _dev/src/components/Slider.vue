<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue'
const emit = defineEmits(['update:modelValue'])



const props = defineProps({
  modelValue: {
    type: [Number, String],
    required: true,
    default: 0
  },
  step: {
    type: Number,
    default: 1
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 100
  }
})

const localValue = ref(props.modelValue)
watch(() => props.modelValue, (newValue) => {
  localValue.value = newValue
})

// Mettez à jour la valeur et émettez le changement
const updateValue = (event) => {
  const newValue = parseInt(event.target.value)
  localValue.value = newValue
  emit('update:modelValue', newValue)
}


</script>

<template>
  <div class="slider flex">
    <input class="bg-indigo-500" type="range" 
      :step="props.step" 
      :min="props.min" 
      :max="props.max" 
      :value="localValue" 
      @input="updateValue" 
    />

    <span class="pl-4">
      {{ props.modelValue }} 
    </span>
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