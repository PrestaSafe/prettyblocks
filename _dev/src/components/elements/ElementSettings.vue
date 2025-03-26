<script setup>
import { ref, computed, watch } from 'vue';
import { trans } from '../../scripts/trans';
import Icon from '../Icon.vue';
import FieldRepeater from '../FieldRepeater.vue';

const props = defineProps({
  element: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update-values', 'close']);

// Fusionner les valeurs actuelles avec les valeurs par défaut des champs
const fields = computed(() => {
  if (!props.element || !props.element.config || !props.element.config.fields) {
    return {};
  }
  
  const result = {};
  
  Object.entries(props.element.config.fields).forEach(([key, field]) => {
    // Cloner le champ pour éviter de modifier l'original
    result[key] = { ...field };
    
    // Appliquer la valeur actuelle si elle existe
    if (props.element.values && props.element.values[key] !== undefined) {
      result[key].value = props.element.values[key];
    } else if (field.default !== undefined) {
      result[key].value = field.default;
    }
  });
  
  return result;
});

// Grouper les champs par onglets
const fieldsByTab = computed(() => {
  const tabs = {};
  
  Object.entries(fields.value).forEach(([key, field]) => {
    const tab = field.tab || 'General';
    
    if (!tabs[tab]) {
      tabs[tab] = {};
    }
    
    tabs[tab][key] = field;
  });
  
  return tabs;
});

// Onglet actif
const activeTab = ref(Object.keys(fieldsByTab.value)[0] || 'General');

// Mettre à jour les valeurs lorsqu'un champ est modifié
const updateField = (key, value) => {
  const values = { ...props.element.values };
  values[key] = value;
  emit('update-values', props.element, values);
};

// Mettre à jour la valeur après une opération de téléchargement
const handleUpdateUpload = () => {
  // Rien à faire, les champs sont déjà liés directement aux valeurs de l'élément
};

// Surveiller les changements dans les onglets disponibles
watch(() => Object.keys(fieldsByTab.value), (newTabs) => {
  if (newTabs.length > 0 && !newTabs.includes(activeTab.value)) {
    activeTab.value = newTabs[0];
  }
});
</script>

<template>
  <div class="element-settings">
    <div class="element-settings-header">
      <h3 class="element-settings-title">
        <img v-if="element.icon_path" :src="element.icon_path" class="h-5 w-5 inline-block mr-2" />
        <Icon v-else :name="element.icon" class="h-5 w-5 inline-block mr-2" />
        {{ element.name }} {{ trans('settings') }}
      </h3>
      
      <button class="element-settings-close" @click="$emit('close')">
        <Icon name="XMarkIcon" class="h-5 w-5" />
      </button>
    </div>
    
    <div class="element-settings-tabs">
      <button
        v-for="tab in Object.keys(fieldsByTab)"
        :key="tab"
        class="element-settings-tab"
        :class="{ 'active': activeTab === tab }"
        @click="activeTab = tab"
      >
        {{ tab }}
      </button>
    </div>
    
    <div class="element-settings-content">
      <template v-for="(tabFields, tab) in fieldsByTab" :key="tab">
        <div v-if="tab === activeTab" class="element-settings-fields">
          <FieldRepeater 
            v-for="(field, key) in tabFields"
            :key="key"
            :field="field"
            @updateUpload="handleUpdateUpload"
            @update:modelValue="(value) => updateField(key, value)"
          />
        </div>
      </template>
    </div>
  </div>
</template>

<style scoped>
.element-settings {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.element-settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #ddd;
}

.element-settings-title {
  font-size: 1.125rem;
  font-weight: 500;
  margin: 0;
}

.element-settings-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border: none;
  background: transparent;
  border-radius: 9999px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.element-settings-close:hover {
  background-color: #eee;
}

.element-settings-tabs {
  display: flex;
  padding: 0 1rem;
  border-bottom: 1px solid #ddd;
  overflow-x: auto;
}

.element-settings-tab {
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  font-weight: 500;
  color: #777;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.element-settings-tab.active {
  color: #5530be;
  box-shadow: inset 0 -2px 0 #5530be;
}

.element-settings-tab:hover {
  color: #5530be;
}

.element-settings-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.element-settings-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
</style>