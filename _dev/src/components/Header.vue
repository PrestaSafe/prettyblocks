<script setup>
import { defineComponent, ref, watch, computed } from 'vue'
import Icon from './Icon.vue';
import Button from './Button.vue';
import ButtonLight from './ButtonLight.vue';
import HeaderDropdown from './HeaderDropdown.vue';
import ZoneSelect from './form/ZoneSelect.vue';
import ShopSelect from './form/ShopSelect.vue';

import { usePrettyBlocksContext } from '../store/pinia';
import { storeToRefs } from 'pinia';


const rightPanel = computed(() => prettyBlocksContext.iframe.rightPanel)
const leftPanel = computed(() => prettyBlocksContext.iframe.leftPanel)
const saveContext = computed(() => prettyBlocksContext.saveContext)
import { trans } from '../scripts/trans'

let prettyBlocksContext = usePrettyBlocksContext() 
defineComponent({
  Icon,
  Button,
  ButtonLight,
  HeaderDropdown,
  ZoneSelect
})

const sizeSelected = ref('w-full')
let context = prettyBlocksContext.psContext
const shop = ref({})

watch(() => prettyBlocksContext.psContext, (newValue) => {
  shop.value = newValue
}, { deep: true })


const state = ref({
  name: "displayHome"
})

const globalSave = () => {
  prettyBlocksContext.emitSaveContext()
}

const changeIframeSize = (width, height, device = null) => {
  sizeSelected.value = width
  prettyBlocksContext.changeIframeSize(width, height, device)
}
let settingsEnabled = ref(true)
const showSettings = () => {
  // prettyBlocksContext.showSettings()
  prettyBlocksContext.displaySettingsPanel()
}

const leaveApp = () => {
  window.open(domain, '_blank');
}
const goBackEnd = () => {
  window.open(adminURL, '_self');
}

const domain = ajax_urls.current_domain
const adminURL = ajax_urls.adminURL
const reloadButton = () => {
  prettyBlocksContext.reloadIframe()
}
</script>

<template>
  <header class="flex justify-between items-center px-4 py-2 border-b border-gray-200">

    <div class="flex items-center gap-2">
      <div class="border-r border-gray-200">
        <ButtonLight @click="goBackEnd" icon="BackspaceIcon" class="p-2" />
        <ButtonLight @click="prettyBlocksContext.updatePanelState('left', leftPanel === 'hide' ? 'default' : 'hide')" :class="leftPanel === 'hide' ? 'bg-black bg-opacity-10 rotate-180' : ''"
          icon="ArrowLeftOnRectangleIcon" class="p-2" />
        <ButtonLight @click="prettyBlocksContext.updatePanelState('left', leftPanel === 'extends' ? 'default' : 'extends')" :class="leftPanel === 'extends' ? 'bg-black bg-opacity-10' : 'rotate-180'"
          icon="ArrowLeftOnRectangleIcon" class="p-2" />
      </div>
      <span>
        <div class="flex items-center">
          <ShopSelect v-model="shop" /> 
          <!-- <Button class="ml-4">
            <Icon name="ArrowPathIcon" @click="reloadButton"/>
          </Button> -->
        </div>
      </span>
    </div>
    <div>
      <!-- header dropdown with demo parameters-->
      <!-- Copyright <a class="text-indigo" href="https://www.prestasafe.com">www.prestasafe.com</a> -->
      <div class="border-gray-200">
        <ButtonLight @click="changeIframeSize('w-full', 'h-full', 'desktop')"
          :class="sizeSelected == 'w-full' ? 'bg-black bg-opacity-10' : ''" icon="ComputerDesktopIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-5/6', 'h-5/6', 'tablet')"
          :class="sizeSelected == 'w-5/6' ? 'bg-black bg-opacity-10 -rotate-90' : '-rotate-90'" icon="DeviceTabletIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-6/12', 'h-full' , 'tablet')"
          :class="sizeSelected == 'w-6/12' ? 'bg-black bg-opacity-10' : ''" icon="DeviceTabletIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-4/12', 'h-full', 'mobile')"
          :class="sizeSelected == 'w-4/12' ? 'bg-black bg-opacity-10' : ''" icon="DevicePhoneMobileIcon"
          class="p-2" />
        <!-- <ButtonLight @click="changeIframeSize('w-3/6', 'h-3/6')" :class="sizeSelected == 'w-4/12' ? 'bg-black bg-opacity-10 -rotate-90' : '-rotate-90'" icon="DevicePhoneMobileIcon" class="p-2" /> -->
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="border-r"> 
        <ButtonLight @click="prettyBlocksContext.updatePanelState('right', rightPanel === 'extends' ? 'default' : 'extends')" :class="rightPanel === 'extends' ? 'bg-black bg-opacity-10' : 'rotate-180'"
          icon="ArrowRightOnRectangleIcon" class="p-2" />
        <ButtonLight @click="showSettings()" :class="saveContext === 'settings' ? 'bg-black bg-opacity-10' : ''"
          icon="WrenchScrewdriverIcon" class="p-2" />
        <ButtonLight @click="prettyBlocksContext.updatePanelState('right', rightPanel === 'hide' ? 'default' : 'hide')" :class="rightPanel === 'hide' ? 'bg-black bg-opacity-10 rotate-180' : ''"
          icon="ArrowRightOnRectangleIcon" class="p-2" />
        <ButtonLight @click="leaveApp" icon="BuildingStorefrontIcon" class="p-2" />
      </div>

      <Button @click="globalSave()" type="primary">{{ trans('save') }}</Button>
    </div>
  </header>
</template>
