<script setup>
import { defineComponent, ref } from 'vue'
import Icon from './Icon.vue';
import Button from './Button.vue';
import ButtonLight from './ButtonLight.vue';
import HeaderDropdown from './HeaderDropdown.vue';
import ZoneSelect from './form/ZoneSelect.vue';
import ShopSelect from './form/ShopSelect.vue';
import emitter from 'tiny-emitter/instance'
import { contextShop } from '../store/currentBlock';
import { trans } from '../scripts/trans'

defineComponent({
  Icon,
  Button,
  ButtonLight,
  HeaderDropdown,
  ZoneSelect
})

const sizeSelected = ref('w-full')
let context = contextShop()
const shop = ref({})
context.$subscribe((mutation, state) => {
  shop.value = state
})

// left panel show and extends
let hideLeftPanel = ref(false)
const hideLeftPanelAction = () => {
  hideLeftPanel.value = !hideLeftPanel.value
  emitter.emit('hideLeftPanelSize', hideLeftPanel.value)
}

let extendLeftPanel = ref(false)
const changeLeftPanelSize = () => {
  extendLeftPanel.value = !extendLeftPanel.value
  emitter.emit('changeLeftPanelSize', extendLeftPanel.value)
}
// right panel show and extends
let hideRightPanel = ref(false)
const hideRightPanelAction = () => {
  hideRightPanel.value = !hideRightPanel.value
  emitter.emit('hideRightPanelSize', hideRightPanel.value)
}
let extendRightPanel = ref(false)
const changeRightPanelSize = () => {
  extendRightPanel.value = !extendRightPanel.value
  emitter.emit('changeRightPanelSize', extendRightPanel.value)
}
const state = ref({
  name: "displayHome"
})

const globalSave = () => {
  emitter.emit('globalSave')
}

const changeIframeSize = (size, height) => {
  sizeSelected.value = size
  emitter.emit('changeIframeSize', size, height)
}

let settingsEnabled = ref(true)
const showSettings = () => {
  settingsEnabled.value = !settingsEnabled.value
  emitter.emit('showSettings', settingsEnabled.value)
}
emitter.on('hideSettings', () => {
  settingsEnabled.value = false
})
const leaveApp = () => {
  window.open(domain, '_blank');
}
const goBackEnd = () => {
  window.open(adminURL, '_self');
}

const domain = ajax_urls.current_domain
const adminURL = ajax_urls.adminURL


</script>

<template>
  <header class="flex justify-between items-center px-4 py-2 border-b border-gray-200">

    <div class="flex items-center gap-2">
      <div class="border-r border-gray-200">
        <ButtonLight @click="goBackEnd" icon="BackspaceIcon" class="p-2" />
        <ButtonLight @click="hideLeftPanelAction" :class="hideLeftPanel ? 'bg-black bg-opacity-10 rotate-180' : ''"
          icon="ArrowLeftOnRectangleIcon" class="p-2" />
        <ButtonLight @click="changeLeftPanelSize" :class="extendLeftPanel ? 'bg-black bg-opacity-10' : 'rotate-180'"
          icon="ArrowLeftOnRectangleIcon" class="p-2" />
      </div>
      <span>
        <div class="flex items-center">
          <ShopSelect v-model="shop" /> 
          <!-- <Button class="ml-4">
            <Icon  name="ArrowPathIcon" @click="emitter.emit('reloadIframe')"/>
          </Button> -->
        </div>
      </span>
    </div>
    <div>
      <!-- header dropdown with demo parameters-->
      <!-- Copyright <a class="text-indigo" href="https://www.prestasafe.com">www.prestasafe.com</a> -->
      <div class="border-gray-200">
        <ButtonLight @click="changeIframeSize('w-full', 'h-full')"
          :class="sizeSelected == 'w-full' ? 'bg-black bg-opacity-10' : ''" icon="ComputerDesktopIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-5/6', 'h-5/6')"
          :class="sizeSelected == 'w-5/6' ? 'bg-black bg-opacity-10 -rotate-90' : '-rotate-90'" icon="DeviceTabletIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-6/12', 'h-full')"
          :class="sizeSelected == 'w-6/12' ? 'bg-black bg-opacity-10' : ''" icon="DeviceTabletIcon"
          class="p-2" />
        <ButtonLight @click="changeIframeSize('w-4/12', 'h-full')"
          :class="sizeSelected == 'w-4/12' ? 'bg-black bg-opacity-10' : ''" icon="DevicePhoneMobileIcon"
          class="p-2" />
        <!-- <ButtonLight @click="changeIframeSize('w-3/6', 'h-3/6')" :class="sizeSelected == 'w-4/12' ? 'bg-black bg-opacity-10 -rotate-90' : '-rotate-90'" icon="DevicePhoneMobileIcon" class="p-2" /> -->
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="border-r">
        <ButtonLight @click="changeRightPanelSize" :class="extendRightPanel ? 'bg-black bg-opacity-10' : 'rotate-180'"
          icon="ArrowRightOnRectangleIcon" class="p-2" />
        <!-- <Icon class="h-5 w-5 inline -mt-[4px]" @click="changeRightPanelSize" name="ArrowRightOnRectangleIcon" /> -->
        <ButtonLight @click="showSettings()" :class="settingsEnabled ? 'bg-black bg-opacity-10' : ''"
          icon="WrenchScrewdriverIcon" class="p-2" />
        <ButtonLight @click="hideRightPanelAction" :class="hideRightPanel ? 'bg-black bg-opacity-10 rotate-180' : ''"
          icon="ArrowRightOnRectangleIcon" class="p-2" />
        <ButtonLight @click="leaveApp" icon="BuildingStorefrontIcon" class="p-2" />
      </div>
      <!-- <div class="border-r border-gray-200">
          <ButtonLight icon="ArrowUturnLeftIcon" class="p-2" />
          <ButtonLight icon="ArrowUturnLeftIcon" class="transform -scale-x-100 p-2" />
        </div> -->
      <Button @click="globalSave()" type="primary">{{ trans('save') }}</button>
    </div>
  </header>
</template>
