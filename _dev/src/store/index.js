import { ref } from 'vue'
import { defineStore } from 'pinia'

/**
 * Left Panel Store
 * used to sync the current selected item between components
 */
export const useLeftPanelStore  = defineStore('leftPanel', () => {
  let selectedElement = ref(null)

  function setSelectedElement(id) {
    selectedElement.value = id
  }

  return { selectedElement, setSelectedElement }
})