<script setup>
import Icon from './Icon.vue';
import Collapsable from './Collapsable.vue';
import MenuItem from './MenuItem.vue';
import ButtonLight from './ButtonLight.vue';
import { defineEmits } from 'vue'
import emitter from 'tiny-emitter/instance'
import { trans } from '../scripts/trans'

defineProps({
  id: String,
  id_prettyblocks: Number,
  title: String,
  icon: String,
  config: Boolean,
  element: Object,
  is_parent: Boolean,
})

const pushEmptyState = (id_prettyblocks) => {
  emit('pushEmptyState', id_prettyblocks)
}

const changeState = (element) => {
  emit('changeState', element)
}

const emit = defineEmits(['pushEmptyState', 'changeState'])
</script>

<template>
  <Collapsable>
    <!-- visible/header part of the collapsable, where we click -->
    <template v-slot:header="props">
      <!-- has the collapse button is inside the menu item, need to pass by a slot -->
      <MenuItem @click="changeState(element)" class="w-full" :id="id" :element="element" :config="config" :title="title"
        :icon="icon">
      <div class="cursor-pointer mr-2" @click="props.collapse" v-if="element.can_repeat">
        <Icon name="ChevronRightIcon" v-if="props.isCollapsed" />
        <Icon name="ChevronDownIcon" v-else />
      </div>
      <div class="cursor-pointer mr-6" v-else>&nbsp;</div>
      </MenuItem>
    </template>
    <!-- the part than can be hidden/collapsed -->
    <template v-slot:content>
      <slot></slot>
      <ButtonLight v-if="element.can_repeat" icon="PlusCircleIcon" @click.prevent="pushEmptyState(id_prettyblocks)"
        class="px-2 py-2 mb-1 rounded-md hover:bg-indigo hover:bg-opacity-10 w-full text-indigo">
        {{ trans('add_new_element') }}
      </ButtonLight>
    </template>
  </Collapsable>
</template>
