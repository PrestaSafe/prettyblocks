<script setup>
import { defineComponent, defineEmits, ref, watch } from 'vue'
import VueFormMultiselect from '@vueform/multiselect'
import { trans } from '../../scripts/trans'

const props = defineProps({
  label: String,
  mode: String,
  modelValue: {
    type: Array,
    default: []
  },
  options: Array,
  searchable: Boolean,
})

defineComponent({
  VueFormMultiselect,
})
let currentOptions = ref([])
if(Array.isArray(props.modelValue))
{
  currentOptions = ref(props.modelValue)
}
const emit = defineEmits(['update:modelValue'])

function onChange(e) {
  emit('update:modelValue', e)
}

watch(currentOptions, onChange)
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<style>
  .multiselect-tag {
    --ms-tag-bg: #99be30;
    --ms-tag-font-size: .75rem;
    --ms-tag-font-weight: 500;
    --ms-tag-line-height: 1.25;
    --ms-tag-py: .25rem;
    white-space: normal;
  }

  .multiselect-tags-search-wrapper {
    width: 100%;
    margin-left: 0;
  }

  input.multiselect-tags-search {
    padding-right: .375rem;
    padding-left: .375rem;
    font-size: .875rem;
  }

  .multiselect-dropdown {
    font-size: .875rem;
  }

  .multiselect-option {
    font-size: inherit;
  }
</style>

<template>
  <div class="text-sm font-medium text-gray-700 my-1">{{ label }}</div>
  <VueFormMultiselect
    v-model="currentOptions"
    :mode="mode"
    :options="options"
    :searchable="searchable"
    :attrs="{ placeholder: trans('type_search_here') }"
    class="mb-2"
  />
</template>
