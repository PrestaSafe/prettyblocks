<script setup>
import { defineComponent, onMounted, ref, reactive } from 'vue'
import Select from './form/Select.vue'
import SimpleSelect from './form/SimpleSelect.vue'
import HeaderDropdown from './HeaderDropdown.vue'
import Input from './form/Input.vue'
import Textarea from './form/Textarea.vue'
import Checkbox from './form/Checkbox.vue'
import Radio from './form/Radio.vue'
import FormControl from './form/FormControl.vue'
import Button from './Button.vue'
import FileUpload from './form/FileUpload.vue'
import Icon from './Icon.vue'
import PanelThemeSettings from './PanelThemeSettings.vue'
import { useStore } from '../store/currentBlock'
import emitter from 'tiny-emitter/instance'

import Loader from './Loader.vue'
import Modal from './Modal.vue'
import Accordion from './Accordion.vue'
import { createToaster } from "@meforma/vue-toaster";
import { v4 as uuidv4 } from 'uuid'
import Editor from '@tinymce/tinymce-vue';
import Block from '../scripts/block'
import ColorInput from 'vue-color-input'
import FieldRepeater from './FieldRepeater.vue'
import { trans } from '../scripts/trans'

const toaster = createToaster({
  position: 'top',
});

defineComponent({
  Select,
  Input,
  Textarea,
  Checkbox,
  Radio,
  FormControl,
  Button,
  HeaderDropdown,
  PanelThemeSettings,
  Icon,
  FileUpload,
  Loader,
  Modal,
  Accordion,
  uuidv4,
  SimpleSelect,
  Editor,
  ColorInput,
  FieldRepeater
})
let showPanel = ref(true)

const displayThemePanel = (e) => {
  showPanel.value = !showPanel.value
}

const state = ref(false)
const config = ref(false)
const showLoader = ref(false)
const blockLoaded = ref(false)

emitter.on('displayBlockConfig', (element) => {
  loadBlockConfig(element)

})

emitter.on('displaySubState', async (element) => {
  // if (blockLoaded.value.id_prettyblocks !== element.id_prettyblocks){
  blockLoaded.value = await Block.loadById(element.id_prettyblocks)
  // }
  getSubState(element)
})

const loadBlock = async (element) => {
  let block = new Block(element)
  blockLoaded.value = block
}
const loadBlockConfig = async (element) => {
  emitter.emit('hideSettings')
  let block = new Block(element)
  blockLoaded.value = block
  let res = await block.loadBlockConfig()
  state.value = false
  hidePanelSettings()
  config.value = res.config
  emitter.emit('scrollInIframe', block.id)
}

const saveConfig = async (success = true) => {
  let block = blockLoaded.value
  if (!block) {
    return alert('No block loaded')
  }

  let res = await block.saveConfig(config.value)
  if (res.message) {
    if (success) {
      toaster.show(res.message)
    }
  }
  emitter.emit('stateUpdated', block.id_prettyblocks)

  if (block.need_reload) {
    emitter.emit('reloadIframe', block.id_prettyblocks)
  }
}

const getSubState = async (element) => {
  emitter.emit('hideSettings')
  // state.value = []
  config.value = false

  let currentBlock = await useStore()
  let currentID = (element != 0) ? element.id_prettyblocks : currentBlock.id_prettyblocks

  let block = blockLoaded.value
  if (!block) {
    alert('no block found when gettings substates')
    return false;
  }
  let key = block.getSubSelectedKey()
  state.value = { ...block.states[key] }
  hidePanelSettings()
  showLoader.value = false
  block.focusOnIframe()
}

const hidePanelSettings = () => {
  showPanel.value = false
  emitter.emit('hideSettings')
}

const showPanelSettings = () => {
  state.value = false
  config.value = false
  showPanel.value = true
}
emitter.off('showSettings')
emitter.on('showSettings', (value) => {
  if (value) {
    showPanelSettings()
  } else {
    hidePanelSettings()
  }
})

/**
 * Save the SubState in BD
 *
 */
const save = async (success = true) => {
  let block = blockLoaded.value
  let data = await block.updateSubSelectItem(state)
  if (data.success) {
    // update done and OK
    if (data.message && success) {
      toaster.show(data.message)
    }
    emitter.emit('initStates')
    if (block.need_reload) {
      emitter.emit('reloadIframe', block.id_prettyblocks)
    } else {
      emitter.emit('stateUpdated', block.id_prettyblocks)
    }
  }
}
emitter.on('globalSave', () => {
  if (config.value) {
    saveConfig()
  }
  if (state.value) {
    save()
  }
})
</script>

<template>
  <div id="rightPanel" class="relative border-l border-gray-200">
    <Loader :visible="showLoader">{{ trans('loading') }}...</Loader>
    <Modal />

    <!-- Config panel -->
    <div v-if="config" id="configPanel" class="absolute top-0 left-0 overflow-y-auto w-full h-full flex flex-col p-2 bg-slate-100" @keyup.enter="saveConfig()">
      <template v-for="f in config" :key="f">
        <FieldRepeater @updateUpload="saveConfig()" :field="f" />
      </template>
      {{ trans('default_settings') }}
      <Checkbox class="my-4" v-model="config.default.container" :title="trans('use_container')" name="container" />
      {{ trans('bg_color') }}
      <div class="flex mb-4 pt-4">
        <ColorInput class="flex-auto rounded-full" v-model="config.default.bg_color" format="hex string" />
        <Input class="flex-auto" :placeholder="trans('ex_color')" v-model="config.default.bg_color" name="bg_color" />
      </div>
      <SimpleSelect v-if="Object.keys(config.templates).length > 1" v-model="config.templateSelected"
        :availableTpl="config.templates" :currentTpl="config.templateSelected"
        @update="value => console.log('value', value)" :label="trans('choose_template')" />
    </div>

    <!-- State panel  -->
    <div v-if="state" id="statePanel" class="absolute top-0 left-0 overflow-y-auto w-full h-full flex flex-col p-2 bg-slate-100" @keyup.enter="save()">
      <template v-for="f in state" :key="f">
        <FieldRepeater @updateUpload="save()" :field="f" />
      </template>
    </div>

    <!-- Theme settings panel  -->
    <div v-if="showPanel" id="themeSettingsPanel" class="absolute top-0 left-0 overflow-y-auto w-full h-full bg-slate-100">
      <div @click="displayThemePanel()" class="bg-indigo text-white">
        <h2 class="ml-4 p-3 text-center">
          <Icon name="CogIcon" class="h-5 w-5 inline" /> {{ trans('theme_settings') }}
        </h2>
      </div>
      <PanelThemeSettings />
    </div>
  </div>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 2s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.tox-notifications-container {
  display: none !important;
}

#rightPanel {
  transition: all 0.5s ease;
}
</style>
