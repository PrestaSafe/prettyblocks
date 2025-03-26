<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePrettyBlocksContext } from '../../store/pinia';
import { HttpClient } from "../../services/HttpClient";
import ElementTree from './ElementTree.vue';
import ElementToolbar from './ElementToolbar.vue';
import ElementSettings from './ElementSettings.vue';
import { trans } from '../../scripts/trans';
import ButtonLight from '../ButtonLight.vue';
let prettyBlocksContext = usePrettyBlocksContext();


const elements = ref([]);
const availableElements = ref({});
const selectedElement = ref(null);
const showSettings = ref(false);
const loading = ref(true);

// Récupérer le bloc actuel
const currentBlock = computed(() => prettyBlocksContext.currentBlock);

onMounted(async () => {
  await loadAvailableElements();
  if (currentBlock.value.id_prettyblocks) {
    await loadElements();
  }
});

// Charger les éléments disponibles
const loadAvailableElements = async () => {
  try {
    const response = await HttpClient.get(ajax_urls.elements);
    availableElements.value = response.elements;
    loading.value = false;
  } catch (error) {
    console.error('Error loading available elements:', error);
    prettyBlocksContext.displayError(trans('error_loading_elements'));
  }
};

// Charger les éléments du bloc actuel
const loadElements = async () => {
  if (!currentBlock.value.id_prettyblocks) return;
  
  try {
    loading.value = true;
    const url = ajax_urls.load_elements.replace('ID_PLACEHOLDER', currentBlock.value.id_prettyblocks);
    const response = await HttpClient.get(url, {
      ctx_id_lang: prettyBlocksContext.psContext.id_lang,
      ctx_id_shop: prettyBlocksContext.psContext.id_shop
    });
    
    elements.value = response.elements || [];
    loading.value = false;
  } catch (error) {
    console.error('Error loading elements:', error);
    prettyBlocksContext.displayError(trans('error_loading_elements'));
    loading.value = false;
  }
};

// Sauvegarder les éléments
const saveElements = async () => {
  if (!currentBlock.value.id_prettyblocks) return;
  
  try {
    loading.value = true;
    const url = ajax_urls.save_elements.replace('ID_PLACEHOLDER', currentBlock.value.id_prettyblocks);
    const response = await HttpClient.post(url, {
      elements: JSON.stringify(elements.value),
      ctx_id_lang: prettyBlocksContext.psContext.id_lang,
      ctx_id_shop: prettyBlocksContext.psContext.id_shop,
      ajax_token: security_app.ajax_token
    });
    
    if (response.success) {
      prettyBlocksContext.displayMessage(response.message);
      if (currentBlock.value.need_reload) {
        prettyBlocksContext.reloadIframe();
      } else {
        prettyBlocksContext.sendPrettyBlocksEvents('reloadBlock', { id_prettyblocks: currentBlock.value.id_prettyblocks });
      }
    } else {
      prettyBlocksContext.displayError(response.message);
    }
    
    loading.value = false;
  } catch (error) {
    console.error('Error saving elements:', error);
    prettyBlocksContext.displayError(trans('error_saving_elements'));
    loading.value = false;
  }
};

// Ajouter un élément à la racine
const addRootElement = (elementType) => {
  if (!availableElements.value[elementType]) return;
  
  const elementPrototype = availableElements.value[elementType];
  const newElement = {
    ...elementPrototype,
    id: 'element_' + Date.now(),
    elements: []
  };
  
  elements.value.push(newElement);
  selectElement(newElement);
};

// Ajouter un élément enfant
const addChildElement = (parentElement, elementType) => {
  if (!availableElements.value[elementType]) return;
  
  const elementPrototype = availableElements.value[elementType];
  const newElement = {
    ...elementPrototype,
    id: 'element_' + Date.now(),
    elements: []
  };
  
  if (!parentElement.elements) {
    parentElement.elements = [];
  }
  
  parentElement.elements.push(newElement);
  selectElement(newElement);
};

// Supprimer un élément
const removeElement = (element, parentElements) => {
  if (!parentElements) {
    // Élément racine
    const index = elements.value.findIndex(e => e.id === element.id);
    if (index !== -1) {
      elements.value.splice(index, 1);
    }
  } else {
    // Élément enfant
    const index = parentElements.findIndex(e => e.id === element.id);
    if (index !== -1) {
      parentElements.splice(index, 1);
    }
  }
  
  selectedElement.value = null;
  showSettings.value = false;
};

// Sélectionner un élément pour édition
const selectElement = (element) => {
  console.log('selectedElement', selectedElement.value)
  selectedElement.value = element;
  showSettings.value = true;
};

// Fonction appelée lors de la mise à jour des valeurs d'un élément
const updateElementValues = (element, values) => {
  element.values = { ...element.values, ...values };
};

// S'inscrire aux événements du contexte
prettyBlocksContext.on('saveElements', saveElements);

// Recharger les éléments quand le bloc actuel change
watch(
  () => prettyBlocksContext.currentBlock.id_prettyblocks,
  async (newId, oldId) => {
    if (newId && newId !== oldId) {
      await loadElements();
    }
  }
);

const closeEditor = () => 
  prettyBlocksContext.$patch({
    currentBlock: {
      id_prettyblocks: null,
      instance_id: null,
      code: null,
      subSelected: null,
      need_reload: true,
      states: []
    }
  });

</script>

<template>
  <div class="element-editor">

    <ButtonLight icon="ArrowLeftIcon" @click="closeEditor" >
      BACK
    </ButtonLight>
    <div v-if="loading" class="element-editor-loading">
      <Loader :visible="true">{{ trans('loading') }}</Loader>
    </div>
    
    <div v-else class="element-editor-container">
      <ElementToolbar
        :available-elements="availableElements"
        @add-element="addRootElement"
        @save-elements="saveElements"
      />
      
      <div class="element-editor-content">
        <div class="element-tree-container" :class="{ 'with-settings': showSettings }">
          
          <ElementTree
            :elements="elements"
            :selected-element="selectedElement"
            @select-element="selectElement"
            @add-child="addChildElement"
            @remove-element="removeElement"
          />
        </div>
        
        <div v-if="showSettings" class="element-settings-container">
          <ElementSettings
            :element="selectedElement"
            @update-values="updateElementValues"
            @close="showSettings = false"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.element-editor {
  position: relative;
  height: 100%;
}

.element-editor-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.element-editor-container {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.element-editor-content {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.element-tree-container {
  flex: 1;
  overflow: auto;
  transition: all 0.3s ease;
}

.element-tree-container.with-settings {
  flex: 0.6;
}

.element-settings-container {
  flex: 0.4;
  overflow: auto;
  border-left: 1px solid #eaeaea;
  background-color: #f9f9f9;
  padding: 1rem;
}
</style>