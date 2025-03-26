<script setup>
import { ref, computed } from 'vue';
import Icon from '../Icon.vue';
import Button from '../Button.vue';
import { trans } from '../../scripts/trans';

const props = defineProps({
  availableElements: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['add-element', 'save-elements']);

// Grouper les éléments par catégorie
const elementsByCategory = computed(() => {
  const categories = {};
  
  Object.values(props.availableElements).forEach(element => {
    if (!categories[element.category]) {
      categories[element.category] = [];
    }
    
    categories[element.category].push(element);
  });
  
  return categories;
});

const addElement = (elementCode) => {
  emit('add-element', elementCode);
};

const saveElements = () => {
  emit('save-elements');
};
</script>

<template>
  <div class="element-toolbar">
    <div class="element-toolbar-section">
      <!-- <span class="element-toolbar-label">{{ trans('add_elements') }}</span> -->
      
      <div v-for="(elements, category) in elementsByCategory" :key="category" class="element-category">
        <span class="element-category-title">{{ category }}</span>
        
        <div class="element-buttons">
          <button
            v-for="element in elements"
            :key="element.code"
            class="element-button"
            @click="addElement(element.code)"
            :title="element.description"
          >
            <img v-if="element.icon_path" :src="element.icon_path" class="h-5 w-5" />
            <Icon v-else :name="element.icon" class="h-5 w-5" />
            <span>{{ element.name }}</span>
          </button>
        </div>
      </div>
    </div>
    
    <div class="element-toolbar-section">
      <Button type="primary" @click="saveElements" icon="SaveIcon">
        {{ trans('save_elements') }}
      </Button>
    </div>
  </div>
</template>

<style scoped>
.element-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background-color: #f0f0f0;
  border-bottom: 1px solid #ddd;
}

.element-toolbar-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.element-toolbar-label {
  font-weight: 500;
  color: #555;
}

.element-category {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.element-category-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #777;
  text-transform: capitalize;
}

.element-buttons {
  display: flex;
  gap: 0.25rem;
}

.element-button {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.75rem;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  color: #333;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.element-button:hover {
  background-color: #f5f5f5;
  border-color: #ccc;
}

.element-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(85, 48, 190, 0.25);
}
</style>