<script setup>
import { defineProps, defineEmits } from 'vue';
import SortableList from '../SortableList.vue';
import ElementNode from './ElementNode.vue';
import { trans } from '../../scripts/trans';
const props = defineProps({
  elements: {
    type: Array,
    required: true
  },
  selectedElement: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['select-element', 'add-child', 'remove-element']);

const handleSelectElement = (element) => {
  emit('select-element', element);
};

const handleAddChild = (parent, elementType) => {
  emit('add-child', parent, elementType);
};

const handleRemoveElement = (element, parentElements) => {
  emit('remove-element', element, parentElements);
};
</script>

<template>
  <div class="element-tree">
    <div v-if="elements.length === 0" class="element-tree-empty">
      <p>{{ trans('no_elements') }}</p>
      <p>{{ trans('add_element_using_toolbar') }}</p>
    </div>
    
    <SortableList v-else :items="elements" group="elements">
      <template v-slot="{ element }">
        <ElementNode
          :element="element"
          :is-selected="selectedElement && selectedElement.id === element.id"
          @select="handleSelectElement"
          @add-child="handleAddChild"
          @remove="() => handleRemoveElement(element, elements)"
        >
          <SortableList
            v-if="element.elements && element.elements.length > 0"
            :items="element.elements"
            :group="'elements-' + element.id"
          >
            <template v-slot="{ element: childElement }">
              <ElementNode
                :element="childElement"
                :is-selected="selectedElement && selectedElement.id === childElement.id"
                @select="handleSelectElement"
                @add-child="handleAddChild"
                @remove="() => handleRemoveElement(childElement, element.elements)"
              >
                <SortableList
                  v-if="childElement.elements && childElement.elements.length > 0"
                  :items="childElement.elements"
                  :group="'elements-' + childElement.id"
                >
                  <template v-slot="{ element: grandChildElement }">
                    <ElementNode
                      :element="grandChildElement"
                      :is-selected="selectedElement && selectedElement.id === grandChildElement.id"
                      @select="handleSelectElement"
                      @add-child="handleAddChild"
                      @remove="() => handleRemoveElement(grandChildElement, childElement.elements)"
                    />
                  </template>
                </SortableList>
              </ElementNode>
            </template>
          </SortableList>
        </ElementNode>
      </template>
    </SortableList>
  </div>
</template>

<style scoped>
.element-tree {
  padding: 1rem;
}

.element-tree-empty {
  text-align: center;
  padding: 2rem;
  color: #888;
}
</style>