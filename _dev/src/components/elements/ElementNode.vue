<script setup>
import { defineProps, defineEmits } from 'vue';
import Icon from '../Icon.vue';
import ButtonLight from '../ButtonLight.vue';

const props = defineProps({
  element: {
    type: Object,
    required: true
  },
  isSelected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['select', 'add-child', 'remove']);

const handleSelect = () => {
  emit('select', props.element);
};

const handleAddChild = (elementType) => {
  emit('add-child', props.element, elementType);
};

const handleRemove = () => {
  if (confirm(trans('confirm_remove_element'))) {
    emit('remove');
  }
};

// Déterminer si l'élément peut avoir des enfants
const canHaveChildren = props.element.category === 'layout';

// Récupérer le titre à afficher
const getElementTitle = () => {
  if (props.element.nameFrom && props.element.values && props.element.values[props.element.nameFrom]) {
    return props.element.values[props.element.nameFrom];
  }
  return props.element.name;
};
</script>

<template>
  <div 
    class="element-node" 
    :class="{ 
      'is-selected': isSelected,
      'can-have-children': canHaveChildren
    }"
    @click.stop="handleSelect"
  >
    <div class="element-node-header">
      <div class="element-node-icon">
        <img v-if="element.icon_path" :src="element.icon_path" class="h-5 w-5" />
        <Icon v-else :name="element.icon" class="h-5 w-5" />
      </div>
      
      <div class="element-node-title">
        {{ getElementTitle() }}
      </div>
      
      <div class="element-node-actions">
        <ButtonLight 
          v-if="canHaveChildren"
          icon="PlusIcon" 
          @click.stop="$emit('show-add-child-menu')" 
          title="Add child element"
        />
        
        <ButtonLight 
          icon="TrashIcon" 
          @click.stop="handleRemove" 
          title="Remove element"
        />
        
        <ButtonLight 
          class="handle cursor-move" 
          icon="ArrowsUpDownIcon" 
          title="Drag to reorder"
        />
      </div>
    </div>
    
    <div v-if="$slots.default" class="element-node-children">
      <slot></slot>
    </div>
  </div>
</template>

<style scoped>
.element-node {
  margin-bottom: 0.5rem;
  border: 2px solid #eaeaea;
  border-radius: 0.25rem;
  background-color: white;
  overflow: hidden;
  transition: all 0.2s ease;
}

.element-node.is-selected {
  border-color: #5530be;
}

.element-node-header {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  cursor: pointer;
  background-color: #f9f9f9;
}

.element-node.is-selected .element-node-header {
  background-color: #e5deff;
}

.element-node-icon {
  margin-right: 0.5rem;
  color: #5530be;
}

.element-node-title {
  flex: 1;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.element-node-actions {
  display: flex;
  gap: 0.25rem;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.element-node:hover .element-node-actions {
  opacity: 1;
}

.element-node-children {
  padding-left: 1.5rem;
  padding-right: 0.5rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  background-color: #fcfcfc;
}

.element-node.can-have-children > .element-node-header::after {
  content: '';
  width: 0;
  height: 0;
  margin-left: 0.5rem;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid #888;
  transition: transform 0.2s ease;
}

.element-node.can-have-children.is-expanded > .element-node-header::after {
  transform: rotate(180deg);
}
</style>